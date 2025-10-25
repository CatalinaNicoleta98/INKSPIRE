<?php
require_once __DIR__ . "/../config.php";

if (!class_exists('UserModel')) {
class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();

        // Ensure hardcoded admin exists
        $this->createHardcodedAdmin();
    }

    private function createHardcodedAdmin() {
        $username = "catalina12";
        $email = "catalina12@example.com";
        $password = "112233";
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $check = $this->conn->prepare("SELECT user_id FROM User WHERE username = :username");
        $check->execute([':username' => $username]);

        if ($check->rowCount() === 0) {
            $stmt = $this->conn->prepare("
                INSERT INTO User (first_name, last_name, email, username, password, is_admin, is_active)
                VALUES ('Catalina', 'Admin', :email, :username, :password, 1, 1)
            ");
            $stmt->execute([
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
        }
    }

    public function register($firstName, $lastName, $email, $username, $password, $dob) {
        // basic validation before checking anything else

        // check email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "invalid_email"; // email not valid
        }

        // check password length
        if (strlen($password) < 6) {
            return "weak_password"; // password too short
        }

        // make sure user is at least 14 years old
        $age = $this->calculateAge($dob);
        if ($age < 14) {
            return "too_young";
        }

        // check if username or email already exists
        $check = $this->conn->prepare("SELECT user_id FROM User WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->rowCount() > 0) {
            return "exists";
        }

        // if everything looks good, insert the new user
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
            return "db_error: " . $error[2]; // for debugging or future logs
        }

        return true; // everything went fine
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
        $age = $today->diff($dobDate)->y;
        return $age;
    }

    public function updateUserInfo($userId, $username, $email) {
        $query = "UPDATE User SET username = :username, email = :email WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }
}
}