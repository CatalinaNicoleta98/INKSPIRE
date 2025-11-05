<?php
require_once __DIR__ . '/../config.php';

class FollowModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Follow a user
    public function followUser($followerId, $followedId) {
        $query = "INSERT INTO Follow (follower_id, following_id) VALUES (:follower_id, :following_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':follower_id', $followerId);
        $stmt->bindParam(':following_id', $followedId);
        return $stmt->execute();
    }

    // Unfollow a user
    public function unfollowUser($followerId, $followedId) {
        $query = "DELETE FROM Follow WHERE follower_id = :follower_id AND following_id = :following_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':follower_id', $followerId);
        $stmt->bindParam(':following_id', $followedId);
        return $stmt->execute();
    }

    // Check if one user follows another
    public function isFollowing($followerId, $followedId) {
        $query = "SELECT 1 FROM Follow WHERE follower_id = :follower_id AND following_id = :following_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':follower_id', $followerId);
        $stmt->bindParam(':following_id', $followedId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // Count followers for a user (exclude admin accounts)
    public function countFollowers($userId) {
        $query = "SELECT COUNT(*) AS count
                  FROM Follow f
                  JOIN User u ON f.follower_id = u.user_id
                  WHERE f.following_id = :user_id
                    AND (u.is_admin IS NULL OR u.is_admin = 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['count']) : 0;
    }

    // Count following for a user (exclude admin accounts)
    public function countFollowing($userId) {
        $query = "SELECT COUNT(*) AS count
                  FROM Follow f
                  JOIN User u ON f.following_id = u.user_id
                  WHERE f.follower_id = :user_id
                    AND (u.is_admin IS NULL OR u.is_admin = 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['count']) : 0;
    }

    // Get detailed list of followers (for hover dropdown) — exclude admin accounts
    public function getFollowersList($userId) {
        $query = "SELECT u.user_id, u.username, p.profile_picture
                  FROM Follow f
                  JOIN User u ON f.follower_id = u.user_id
                  LEFT JOIN Profile p ON u.user_id = p.user_id
                  WHERE f.following_id = :user_id
                    AND (u.is_admin IS NULL OR u.is_admin = 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get detailed list of following users (exclude admin accounts)
    public function getFollowingList($userId) {
        $query = "SELECT u.user_id, u.username, p.profile_picture
                  FROM Follow f
                  JOIN User u ON f.following_id = u.user_id
                  LEFT JOIN Profile p ON u.user_id = p.user_id
                  WHERE f.follower_id = :user_id
                    AND (u.is_admin IS NULL OR u.is_admin = 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
