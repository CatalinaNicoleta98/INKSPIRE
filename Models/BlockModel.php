

<?php
require_once __DIR__ . '/../config.php';

class BlockModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Block a user
    public function blockUser($blockerId, $blockedId) {
        $query = "INSERT INTO Block (blocker_id, blocked_id) VALUES (:blocker_id, :blocked_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocker_id', $blockerId);
        $stmt->bindParam(':blocked_id', $blockedId);
        return $stmt->execute();
    }

    // Unblock a user
    public function unblockUser($blockerId, $blockedId) {
        $query = "DELETE FROM Block WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocker_id', $blockerId);
        $stmt->bindParam(':blocked_id', $blockedId);
        return $stmt->execute();
    }

    // Check if a user is blocked
    public function isBlocked($blockerId, $blockedId) {
        $query = "SELECT 1 FROM Block WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocker_id', $blockerId);
        $stmt->bindParam(':blocked_id', $blockedId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // Get all users blocked by a specific user
    public function getBlockedUsers($blockerId) {
        $query = "SELECT blocked_id FROM Block WHERE blocker_id = :blocker_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocker_id', $blockerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get all users who have blocked a specific user
    public function getUsersBlocking($blockedId) {
        $query = "SELECT blocker_id FROM Block WHERE blocked_id = :blocked_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocked_id', $blockedId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>