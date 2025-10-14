<?php
require_once __DIR__ . "/../config.php";

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($firstName, $lastName, $email, $username, $password) {
        $query = "INSERT INTO User (first_name, last_name, email, username, password) 
                  VALUES (:first_name, :last_name, :email, :username, :password)";
        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        return $stmt->execute([
            ':first_name' => htmlspecialchars($firstName),
            ':last_name'  => htmlspecialchars($lastName),
            ':email'      => htmlspecialchars($email),
            ':username'   => htmlspecialchars($username),
            ':password'   => $hashedPassword
        ]);
    }

    public function login($username, $password) {
        $query = "SELECT * FROM User WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => htmlspecialchars($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>