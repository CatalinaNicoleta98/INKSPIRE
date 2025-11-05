<?php
require_once __DIR__ . '/../config.php';

class LikeModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Check if a user already liked a post
    public function userLiked($userId, $postId) {
        $query = "SELECT 1 FROM `Like` WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
        return (bool)$stmt->fetch();
    }

    // Add a new like
    public function addLike($userId, $postId) {
        $stmt = $this->conn->prepare("INSERT INTO `Like` (user_id, post_id) VALUES (:user_id, :post_id)");
        return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
    }

    // Remove an existing like
    public function removeLike($userId, $postId) {
        $stmt = $this->conn->prepare("DELETE FROM `Like` WHERE user_id = :user_id AND post_id = :post_id");
        return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
    }

    // Count likes on a specific post
    public function countLikes($postId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM `Like` WHERE post_id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        return (int)$stmt->fetchColumn();
    }

    // Get all liked posts by a user
    public function getUserLikes($userId) {
        $stmt = $this->conn->prepare("SELECT post_id FROM `Like` WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // Delete all likes between two users (used for blocking)
    public function deleteInteractionsBetween($userA, $userB) {
        $query = "DELETE FROM `Like`
                  WHERE post_id IN (
                      SELECT p.post_id FROM Post p WHERE p.user_id IN (:userA, :userB)
                  )
                  AND user_id IN (:userA, :userB)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userA', $userA);
        $stmt->bindParam(':userB', $userB);
        return $stmt->execute();
    }
}
?>