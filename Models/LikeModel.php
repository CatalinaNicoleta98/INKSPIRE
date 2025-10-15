<?php
require_once __DIR__ . "/../config.php";

class LikeModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function userLiked($userId, $postId) {
        $query = "SELECT 1 FROM `Like` WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
        return $stmt->fetch() ? true : false;
    }

    public function addLike($userId, $postId) {
        $stmt = $this->conn->prepare("INSERT INTO `Like` (user_id, post_id) VALUES (:user_id, :post_id)");
        return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
    }

    public function removeLike($userId, $postId) {
        $stmt = $this->conn->prepare("DELETE FROM `Like` WHERE user_id = :user_id AND post_id = :post_id");
        return $stmt->execute([':user_id' => $userId, ':post_id' => $postId]);
    }

    public function countLikes($postId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM `Like` WHERE post_id = :post_id");
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchColumn();
    }

    // Get all liked posts by a user (for loading feed efficiently)
    public function getUserLikes($userId) {
        $stmt = $this->conn->prepare("SELECT post_id FROM `Like` WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>