<?php
require_once __DIR__ . '/../config.php';

if (!class_exists('UserModel')) {
class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($firstName, $lastName, $email, $username, $password, $dob) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "invalid_email";
        }

        // Validate password length
        if (strlen($password) < 6) {
            return "weak_password";
        }

        // Validate age
        $age = $this->calculateAge($dob);
        if ($age < 14) {
            return "too_young";
        }

        // Check for existing username or email
        $check = $this->conn->prepare("SELECT user_id FROM User WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->rowCount() > 0) {
            return "exists";
        }

        // Insert new user
        $query = "INSERT INTO User (first_name, last_name, email, username, password, DOB) 
                  VALUES (:first_name, :last_name, :email, :username, :password, :dob)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if (!$stmt->execute([
            ':first_name' => htmlspecialchars($firstName),
            ':last_name'  => htmlspecialchars($lastName),
            ':email'      => htmlspecialchars($email),
            ':username'   => htmlspecialchars($username),
            ':password'   => $hashedPassword,
            ':dob'        => $dob
        ])) {
            $error = $stmt->errorInfo();
            return "db_error: " . $error[2];
        }

        // Automatically create a Profile entry for the new user
        $userId = $this->conn->lastInsertId();
        $profileQuery = "INSERT INTO Profile (user_id, display_name, profile_picture, bio, followers, posts, is_private)
                         VALUES (:user_id, '', 'uploads/default.png', '', 0, 0, 0)";
        $profileStmt = $this->conn->prepare($profileQuery);
        $profileStmt->bindParam(':user_id', $userId);
        $profileStmt->execute();

        return true;
    }

    public function login($username, $password) {
        $query = "SELECT * FROM User WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => htmlspecialchars($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Allow login for both active and admin-blocked users.
            // Blocking behavior is enforced across controllers using is_active.
            return $user;
        }
        return false;
    }

    private function calculateAge($dob) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        return $today->diff($dobDate)->y;
    }

    public function updateUserInfo($userId, $username, $email) {
        $query = "UPDATE User 
                  SET username = :username, email = :email 
                  WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    // Get user by ID
    public function getUserById($userId) {
        $query = "SELECT * FROM User WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Verify password for a user by ID
    public function verifyPasswordById($userId, $password) {
        try {
            $query = "SELECT password FROM User WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return false;
            }

            return password_verify($password, $row['password']);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete user by ID (cascade will remove profile, posts, comments, likes, follows)
    public function deleteUserById($userId) {
        try {
            $query = "DELETE FROM User WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    // Fetch suggested users (exclude self and users already followed)
    public function getSuggestedUsers($currentUserId, $limit = 5) {
        try {
            $query = "
                SELECT 
                    u.user_id, 
                    u.username, 
                    p.profile_picture
                FROM User u
                INNER JOIN Profile p ON u.user_id = p.user_id
                WHERE u.user_id != :currentUserId
                  AND u.is_active = 1
                  AND u.user_id NOT IN (
                      SELECT following_id 
                      FROM Follow 
                      WHERE follower_id = :currentUserId
                  )
                  AND u.user_id NOT IN (
                      SELECT blocked_id 
                      FROM Block 
                      WHERE blocker_id = :currentUserId
                      UNION
                      SELECT blocker_id 
                      FROM Block 
                      WHERE blocked_id = :currentUserId
                  )
                ORDER BY RAND()
                LIMIT :limit
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':currentUserId', $currentUserId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }
    /** 
     * Admin overview: list all users with followers and posts count
     */
    public function getAdminUserOverview() {
        $query = "
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.is_active,
                u.is_admin,
                p.profile_picture,
                p.followers,
                p.posts
            FROM User u
            INNER JOIN Profile p ON u.user_id = p.user_id
            ORDER BY u.username ASC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Daily stats using views (must exist in DB):
     * view_daily_user_stats and view_daily_post_stats
     */
    public function getAdminDailyStats() {
        $stats = [
            'new_users_today' => 0,
            'new_posts_today' => 0
        ];

        try {
            $q1 = $this->conn->query("SELECT new_users_today FROM view_daily_user_stats LIMIT 1");
            $row1 = $q1->fetch(PDO::FETCH_ASSOC);
            if ($row1 && isset($row1['new_users_today'])) {
                $stats['new_users_today'] = (int)$row1['new_users_today'];
            }
        } catch (PDOException $e) {}

        try {
            $q2 = $this->conn->query("SELECT new_posts_today FROM view_daily_post_stats LIMIT 1");
            $row2 = $q2->fetch(PDO::FETCH_ASSOC);
            if ($row2 && isset($row2['new_posts_today'])) {
                $stats['new_posts_today'] = (int)$row2['new_posts_today'];
            }
        } catch (PDOException $e) {}

        return $stats;
    }

    /**
     * Toggle global block/unblock
     */
    public function setGlobalBlock($userId, $blocked) {
        $query = "UPDATE User SET is_active = :active WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':active' => $blocked ? 0 : 1,
            ':user_id' => $userId
        ]);
    }

    /**
     * Set global block status (admin-wide block)
     */
    public function setGlobalBlockStatus($userId, $status) {
        $query = "UPDATE User SET is_globally_blocked = :status WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Check if a user is globally blocked
     */
    public function isGloballyBlocked($userId) {
        $query = "SELECT is_globally_blocked FROM User WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Promote or demote admin
     */
    public function setAdminFlag($userId, $isAdmin) {
        $query = "UPDATE User SET is_admin = :admin WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':admin' => $isAdmin ? 1 : 0,
            ':user_id' => $userId
        ]);
    }
    /**
     * Set password reset token and expiration for a user by email.
     */
    public function setResetToken($email, $token, $expires) {
        $query = "UPDATE `User`
                  SET reset_token = :token, reset_expires = :expires
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':email' => $email
        ]);
    }

    /**
     * Find a user by reset token (must not be expired).
     */
    public function findUserByResetToken($token) {
        $query = "SELECT *
                  FROM `User`
                  WHERE reset_token = :token
                  AND reset_expires > NOW()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update a user's password via reset token.
     */
    public function updatePasswordByToken($token, $hashedPassword) {
        $query = "UPDATE `User`
                  SET password = :password
                  WHERE reset_token = :token
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':token' => $token
        ]);
    }

    /**
     * Clear reset token after successful password change.
     */
    public function clearResetToken($token) {
        $query = "UPDATE `User`
                  SET reset_token = NULL,
                      reset_expires = NULL
                  WHERE reset_token = :token
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':token' => $token]);
    }
    }
}
?>