<?php
require_once __DIR__ . '/../config.php';

class ProfileModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Update profile bio and picture
    public function updateProfileInfo($userId, $bio, $profilePicture) {
        $query = "UPDATE Profile 
                  SET bio = :bio, profile_picture = :profile_picture 
                  WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':profile_picture', $profilePicture);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    // Get list of following profile IDs for a given user
    public function getFollowingIds($userId) {
        $query = "SELECT following_id 
                  FROM Follow 
                  JOIN Profile ON Follow.follower_id = Profile.profile_id 
                  WHERE Profile.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $results ?: [];
    }

    // Get user profile details
    public function getUserProfile($userId) {
        $query = "SELECT p.*, u.username, u.email 
                  FROM Profile p 
                  JOIN User u ON p.user_id = u.user_id 
                  WHERE p.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>