<?php
require_once __DIR__ . '/../config.php';

class CommentModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add a new comment
    public function addComment($post_id, $user_id, $content) {
        $stmt = $this->conn->prepare("INSERT INTO `comment` (post_id, user_id, text, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$post_id, $user_id, htmlspecialchars(trim($content))]);
    }

    // Get all comments for a specific post
    public function getCommentsByPost($post_id) {
        $stmt = $this->conn->prepare("
            SELECT c.comment_id, c.text, c.created_at, u.username 
            FROM `comment` c
            JOIN `user` u ON c.user_id = u.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
