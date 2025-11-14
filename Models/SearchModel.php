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
            SELECT u.user_id, u.username, p.profile_picture
            FROM User u
            JOIN Profile p ON u.user_id = p.user_id
            WHERE u.username LIKE :q
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

    // Search posts by matching tags, return full basic post data
    public function searchPostsByTag($q, $limit = 50) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.post_id,
                p.user_id,
                p.title,
                p.description,
                p.image_url,
                p.tags,
                p.created_at,
                p.is_public,
                u.username,
                pr.profile_picture
            FROM Post p
            JOIN User u ON p.user_id = u.user_id
            JOIN Profile pr ON p.user_id = pr.user_id
            WHERE p.tags LIKE :q
            LIMIT :limit
        ");
        $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
}
