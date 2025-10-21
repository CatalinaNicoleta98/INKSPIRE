<?php
require_once __DIR__ . '/../config.php';

class CommentModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add a new comment
    public function addComment($post_id, $user_id, $text) {
        $stmt = $this->conn->prepare("INSERT INTO `comment` (post_id, user_id, text, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$post_id, $user_id, htmlspecialchars(trim($text))]);
    }

    // Get all comments for a specific post, include ownership flag
    public function getCommentsByPost($post_id, $current_user_id = null) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.comment_id, 
                c.text, 
                c.created_at, 
                u.username,
                (c.user_id = :current_user_id) AS owned
            FROM `comment` c
            JOIN `user` u ON c.user_id = u.user_id
            WHERE c.post_id = :post_id
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([
            ':post_id' => $post_id,
            ':current_user_id' => $current_user_id ?? 0
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete a comment (only if it belongs to the current user)
    public function deleteComment($comment_id, $user_id) {
        $stmt = $this->conn->prepare("DELETE FROM `comment` WHERE comment_id = ? AND user_id = ?");
        return $stmt->execute([$comment_id, $user_id]);
    }
}
?>
