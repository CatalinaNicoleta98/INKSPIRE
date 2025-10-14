<?php
require_once __DIR__ . "/../config.php";

class PostModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Create new post
    public function createPost($title, $description, $imagePath, $userId, $tags = null) {
        $query = "INSERT INTO Post (title, description, image_url, user_id) VALUES (:title, :description, :image_url, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':title' => htmlspecialchars($title),
            ':description' => htmlspecialchars($description),
            ':image_url' => htmlspecialchars($imagePath),
            ':user_id' => $userId
        ]);

        $postId = $this->conn->lastInsertId();

        // Optional: store tags later when we add them
        return $postId;
    }

    // Fetch all posts (for feed)
    public function getAllPosts() {
        $query = "SELECT p.*, u.username 
                  FROM Post p 
                  JOIN User u ON p.user_id = u.user_id 
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>