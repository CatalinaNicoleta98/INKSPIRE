<?php
require_once __DIR__ . "/../config.php";

class PostModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Create a new post
    public function createPost($title, $description, $imagePath, $userId, $tags = null) {
        try {
            $query = "INSERT INTO Post (title, description, image_url, user_id, tags)
                      VALUES (:title, :description, :image_url, :user_id, :tags)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':image_url' => $imagePath,
                ':user_id' => $userId,
                ':tags' => $tags
            ]);
            return true;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Get all posts (used for Explore)
    public function getAllPosts() {
        $query = "SELECT p.*, u.username,
                         (SELECT COUNT(*) FROM `like` l WHERE l.post_id = p.post_id) AS likes,
                         (SELECT COUNT(*) FROM `comment` c WHERE c.post_id = p.post_id) AS comments
                  FROM post p
                  JOIN user u ON p.user_id = u.user_id
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get one post by ID
    public function getPostById($postId) {
        $query = "SELECT p.*, u.username 
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  WHERE p.post_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get posts for multiple users (Home feed)
    public function getPostsForUsers($userIds) {
        if (empty($userIds)) return [];

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "SELECT p.*, u.username,
                       (SELECT COUNT(*) FROM `like` l WHERE l.post_id = p.post_id) AS likes,
                       (SELECT COUNT(*) FROM `comment` c WHERE c.post_id = p.post_id) AS comments
                FROM post p
                JOIN user u ON p.user_id = u.user_id
                WHERE p.user_id IN ($placeholders)
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($userIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get posts by a specific user (Profile)
    public function getPostsByUser($userId) {
        $query = "SELECT p.*, u.username,
                         (SELECT COUNT(*) FROM `like` l WHERE l.post_id = p.post_id) AS likes,
                         (SELECT COUNT(*) FROM `comment` c WHERE c.post_id = p.post_id) AS comments
                  FROM post p
                  JOIN user u ON p.user_id = u.user_id
                  WHERE p.user_id = :user_id
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>