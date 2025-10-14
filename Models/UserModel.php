<?php
require_once __DIR__ . "/../config.php";

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($firstName, $lastName, $email, $username, $password, $dob) {
        // Calculate age
        $age = $this->calculateAge($dob);
        if ($age < 14) {
            return "too_young";
        }

        // Check if username or email already exists
        $check = $this->conn->prepare("SELECT user_id FROM User WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->rowCount() > 0) {
            return "exists";
        }

        $query = "INSERT INTO User (first_name, last_name, email, username, password, DOB) 
                  VALUES (:first_name, :last_name, :email, :username, :password, :dob)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt->execute([
            ':first_name' => htmlspecialchars($firstName),
            ':last_name'  => htmlspecialchars($lastName),
            ':email'      => htmlspecialchars($email),
            ':username'   => htmlspecialchars($username),
            ':password'   => $hashedPassword,
            ':dob'        => $dob
        ]);

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
        $age = $today->diff($dobDate)->y;
        return $age;
    }
}
?>