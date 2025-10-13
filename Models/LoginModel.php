<?php
require_once __DIR__ . '/Database.php';

class LoginModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function findUser(string $usernameOrEmail): ?array {
        $stmt = $this->db->prepare("SELECT * FROM User WHERE username = :val OR email = :val LIMIT 1");
        $stmt->execute([':val' => $usernameOrEmail]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}