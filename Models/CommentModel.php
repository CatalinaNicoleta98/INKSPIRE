<?php

class CommentModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDb() {
        return $this->conn;
    }

    // Add a new comment
    public function addComment($post_id, $user_id, $text, $parent_id = null) {
        $stmt = $this->conn->prepare("INSERT INTO `Comment` (post_id, user_id, text, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$post_id, $user_id, htmlspecialchars(trim($text)), $parent_id])) {
            return $this->conn->lastInsertId(); // Return the new comment_id for immediate use
        }
        return false;
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
            FROM `Comment` c
            JOIN `User` u ON c.user_id = u.user_id
            WHERE c.post_id = :post_id
            AND u.is_active = 1
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
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM `Comment` c
            JOIN User u ON c.user_id = u.user_id
            WHERE c.post_id = ?
            AND u.is_active = 1
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    // Delete a comment (normal user must own it, admin can delete any)
    public function deleteComment($comment_id, $user_id = null) {
        try {
            if ($user_id === null) {
                // Admin deletion — ignore ownership
                $stmt = $this->conn->prepare("DELETE FROM `Comment` WHERE comment_id = ?");
                $stmt->execute([$comment_id]);
            } else {
                // Normal user — must own the comment
                $stmt = $this->conn->prepare("DELETE FROM `Comment` WHERE comment_id = ? AND user_id = ?");
                $stmt->execute([$comment_id, $user_id]);
            }

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/../debug_sql.txt', $e->getMessage());
            return false;
        }
    }

    // Edit a comment (only if it belongs to the current user)
    public function updateComment($comment_id, $user_id, $text) {
        $stmt = $this->conn->prepare("UPDATE `Comment` SET text = ?, updated_at = NOW() WHERE comment_id = ? AND user_id = ?");
        return $stmt->execute([htmlspecialchars(trim($text)), $comment_id, $user_id]);
    }

    // Get post_id for a given comment
    public function getPostIdByComment($comment_id) {
        $stmt = $this->conn->prepare("SELECT post_id FROM `Comment` WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['post_id'] : null;
    }

    // Delete all comments between two users (used for blocking)
    public function deleteInteractionsBetween($userA, $userB) {
        $query = "DELETE FROM `Comment`
                  WHERE post_id IN (
                      SELECT p.post_id FROM Post p WHERE p.user_id IN (:userA, :userB)
                  )
                  AND user_id IN (:userA, :userB)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userA', $userA);
        $stmt->bindParam(':userB', $userB);
        return $stmt->execute();
    }
    // Get single comment by ID (used for notification validation)
    public function getCommentById($comment_id) {
        $stmt = $this->conn->prepare("SELECT * FROM `Comment` WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>