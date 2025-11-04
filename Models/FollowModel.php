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

    // Count followers for a user
    public function countFollowers($userId) {
        $query = "SELECT COUNT(*) AS count FROM Follow WHERE following_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['count']) : 0;
    }

    // Count following for a user
    public function countFollowing($userId) {
        $query = "SELECT COUNT(*) AS count FROM Follow WHERE follower_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? intval($result['count']) : 0;
    }

    // Get detailed list of followers (for hover dropdown)
    public function getFollowersList($userId) {
        $query = "SELECT u.user_id, u.username, p.profile_picture
                  FROM Follow f
                  JOIN Profile p ON f.follower_id = p.profile_id
                  JOIN User u ON p.user_id = u.user_id
                  WHERE f.following_id = (
                      SELECT profile_id FROM Profile WHERE user_id = :user_id
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get detailed list of following users (for hover dropdown)
    public function getFollowingList($userId) {
        $query = "SELECT u.user_id, u.username, p.profile_picture
                  FROM Follow f
                  JOIN Profile p ON f.following_id = p.profile_id
                  JOIN User u ON p.user_id = u.user_id
                  WHERE f.follower_id = (
                      SELECT profile_id FROM Profile WHERE user_id = :user_id
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
