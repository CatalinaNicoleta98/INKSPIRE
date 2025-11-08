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
            if (!$user['is_active']) {
                return "blocked";
            }
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
}
}
?>