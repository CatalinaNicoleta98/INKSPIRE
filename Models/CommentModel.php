<?php
require_once __DIR__ . '/../config.php';

class CommentModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add a new comment
    public function addComment($post_id, $user_id, $text, $parent_id = null) {
        $stmt = $this->conn->prepare("INSERT INTO `comment` (post_id, user_id, text, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$post_id, $user_id, htmlspecialchars(trim($text)), $parent_id]);
    }

    // Get all comments for a specific post, including ownership information
    public function getCommentsByPost($post_id, $current_user_id = null) {
        $sql = "
            SELECT 
                c.comment_id, 
                c.user_id, 
                c.text, 
                c.parent_id, 
                c.created_at, 
                u.username,
                CASE WHEN c.user_id = :current_user THEN 1 ELSE 0 END AS owned
            FROM `comment` c
            JOIN `user` u ON c.user_id = u.user_id
            WHERE c.post_id = :post_id
            ORDER BY c.created_at ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':post_id' => $post_id,
            ':current_user' => $current_user_id ?? 0
        ]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize comments into nested structure
        $commentTree = [];
        $commentRefs = [];

        foreach ($comments as &$comment) {
            $comment['replies'] = [];
            $comment['owned'] = (bool)$comment['owned'];
            $commentRefs[$comment['comment_id']] = &$comment;
            if ($comment['parent_id'] === null) {
                $commentTree[] = &$comment;
            } else {
                if (isset($commentRefs[$comment['parent_id']])) {
                    $commentRefs[$comment['parent_id']]['replies'][] = &$comment;
                }
            }
        }

        return $commentTree;
    }

    // Count total comments (including replies) for a specific post
    public function countCommentsByPost($post_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM `comment` WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    // Delete a comment (only if it belongs to the current user)
    public function deleteComment($comment_id, $user_id) {
        $stmt = $this->conn->prepare("DELETE FROM `comment` WHERE comment_id = ? AND user_id = ?");
        return $stmt->execute([$comment_id, $user_id]);
    }

    // Edit a comment (only if it belongs to the current user)
    public function updateComment($comment_id, $user_id, $text) {
        $stmt = $this->conn->prepare("UPDATE `comment` SET text = ?, updated_at = NOW() WHERE comment_id = ? AND user_id = ?");
        return $stmt->execute([htmlspecialchars(trim($text)), $comment_id, $user_id]);
    }

    // Get post_id for a given comment
    public function getPostIdByComment($comment_id) {
        $stmt = $this->conn->prepare("SELECT post_id FROM `comment` WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['post_id'] : null;
    }
}
?>
