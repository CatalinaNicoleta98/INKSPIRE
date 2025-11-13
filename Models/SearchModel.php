<?php
require_once __DIR__ . '/../config.php';

if (!class_exists('SearchModel')) {
class SearchModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Search usernames
    public function searchUsers($q, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT user_id, username 
            FROM User 
            WHERE username LIKE :q 
            LIMIT :limit
        ");
        $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search post titles
    public function searchTitles($q, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT post_id, title 
            FROM Post 
            WHERE title LIKE :q 
            LIMIT :limit
        ");
        $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search tags (from tags column)
    public function searchTags($q, $limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT tags 
            FROM Post 
            WHERE tags LIKE :q 
            LIMIT :limit
        ");
        $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
}
