<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/ProfileModel.php';

class SettingsController {
    private $userModel;
    private $profileModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
    }

    public function update() {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $user = Session::get('user');
        $userId = $user['user_id'];

        // Ensure user has a profile record before updating
        $existingProfile = $this->profileModel->getProfileByUserId($userId);
        if (!$existingProfile) {
            // Create a new profile if missing
            if ($this->profileModel->createProfile($userId)) {
                error_log("Created missing profile for user ID: " . $userId);
            } else {
                error_log("Failed to create missing profile for user ID: " . $userId);
            }
        }

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $bio = trim($_POST['bio']);
        // Retrieve privacy setting (0 = public, 1 = private)
        $isPrivate = isset($_POST['is_private']) ? 1 : 0;

        // Handle profile picture upload
        $profilePicture = $user['profile_picture'] ?? null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $targetDir = __DIR__ . '/../uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicture = 'uploads/' . $fileName;
            } else {
                error_log("Failed to move uploaded file for user ID: " . $userId);
            }
        }

        // Update user data (username and email)
        $this->userModel->updateUserInfo($userId, $username, $email);

        // Update profile data (bio, profile picture, and privacy)
        if (!$this->profileModel->updateProfileInfo($userId, $bio, $profilePicture, $isPrivate)) {
            error_log("Failed to update profile info for user ID: " . $userId);
        }

        // Refresh session with updated user data
        $updatedUser = $this->userModel->getUserById($userId);
        $updatedUser['bio'] = $bio;
        $updatedUser['profile_picture'] = $profilePicture;
        $updatedUser['is_private'] = $isPrivate;
        Session::set('user', $updatedUser);

        header('Location: index.php?action=settings&success=1');
        exit();
    }
}
?>