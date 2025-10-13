<?php
require_once __DIR__ . '/Database.php';

class RegisterModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function userExists(string $username, string $email): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM User WHERE username = :u OR email = :e");
        $stmt->execute([':u' => $username, ':e' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function createUser(array $data): bool {
        $sql = "INSERT INTO User (first_name, last_name, email, username, password, DOB, is_admin, is_active)
                VALUES (:fn, :ln, :em, :un, :pw, :dob, 0, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':fn' => $data['first_name'],
            ':ln' => $data['last_name'],
            ':em' => $data['email'],
            ':un' => $data['username'],
            ':pw' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':dob' => $data['dob'] ?: null
        ]);
    }
}