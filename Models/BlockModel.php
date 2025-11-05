<?php
require_once __DIR__ . '/../config.php';

class BlockModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Block a user (idempotent – avoids duplicates)
    public function blockUser($blockerId, $blockedId) {
        if ($this->isBlocked($blockerId, $blockedId)) {
            return true; // already blocked – treat as success
        }
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

    // Get full info for users blocked by a specific user (for UI)
    public function getBlockedUsers($blockerId) {
        $query = "SELECT u.user_id, u.username, p.profile_picture
                  FROM Block b
                  JOIN User u ON b.blocked_id = u.user_id
                  LEFT JOIN Profile p ON u.user_id = p.user_id
                  WHERE b.blocker_id = :blocker_id
                  ORDER BY u.username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocker_id', $blockerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all users who have blocked a specific user
    public function getUsersBlocking($blockedId) {
        $query = "SELECT blocker_id FROM Block WHERE blocked_id = :blocked_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':blocked_id', $blockedId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Check if either user has blocked the other
    public function isEitherBlocked($userA, $userB) {
        $query = "SELECT 1 FROM Block 
                  WHERE (blocker_id = :userA AND blocked_id = :userB)
                     OR (blocker_id = :userB AND blocked_id = :userA)
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userA', $userA);
        $stmt->bindParam(':userB', $userB);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
?>