<?php

class ProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDb() {
        return $this->conn;
    }

    // Update profile bio, picture, and privacy setting
    public function updateProfileInfo($userId, $bio, $profilePicture, $isPrivate) {
        $query = "UPDATE Profile 
                  SET bio = :bio, 
                      profile_picture = :profile_picture,
                      is_private = :is_private
                  WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':profile_picture', $profilePicture);
        $stmt->bindParam(':is_private', $isPrivate, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId);
        $success = $stmt->execute();
        if (!$success) {
            $error = $stmt->errorInfo();
            error_log("Profile update failed for user ID {$userId}: " . print_r($error, true));
        }
        return $success;
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
        $query = "SELECT p.*, u.username, u.email, u.is_active 
                  FROM Profile p 
                  JOIN User u ON p.user_id = u.user_id 
                  WHERE p.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Check if a profile exists by user ID
    public function getProfileByUserId($userId) {
        $query = "SELECT * FROM Profile WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a default profile for a user
    public function createProfile($userId) {
        $query = "INSERT INTO Profile (user_id, display_name, profile_picture, bio, followers, posts, is_private)
                  VALUES (:user_id, '', 'uploads/default.png', '', 0, 0, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }
}
?>