<?php
require_once __DIR__ . "/../config.php";

class PostModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

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

    public function getAllPosts() {
        $query = "SELECT p.*, u.username 
                  FROM Post p
                  JOIN User u ON p.user_id = u.user_id
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getPostById($postId) {
        $query = "SELECT p.*, u.username 
                FROM Post p
                JOIN User u ON p.user_id = u.user_id
                WHERE p.post_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
?>