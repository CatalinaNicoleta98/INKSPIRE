<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/ImageResizer.php';
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
            $this->profileModel->createProfile($userId);
            $existingProfile = $this->profileModel->getProfileByUserId($userId);
        }

        // Preserve old values unless changed
        $username = !empty($_POST['username']) ? trim($_POST['username']) : ($user['username'] ?? '');
        $email = !empty($_POST['email']) ? trim($_POST['email']) : ($user['email'] ?? '');
        $bio = isset($_POST['bio']) ? trim($_POST['bio']) : ($existingProfile['bio'] ?? '');
        $isPrivate = isset($_POST['is_private']) ? 1 : ($existingProfile['is_private'] ?? 0);
        $profilePicture = $existingProfile['profile_picture'] ?? '';

        // Handle new profile picture upload if one is provided
        if (!empty($_FILES['profile_picture']['name'])) {
            $targetDir = __DIR__ . '/../uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;
            $fileTmp = $_FILES['profile_picture']['tmp_name'];
            $fileMime = mime_content_type($fileTmp);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($fileMime, $allowedTypes) && move_uploaded_file($fileTmp, $targetFile)) {
                // Resize the uploaded profile picture to 512x512 max
                ImageResizer::resizeImage($targetFile, 512, 512);
                $profilePicture = 'uploads/' . $fileName;
            } else {
                error_log("Invalid or failed image upload for user ID: " . $userId);
            }
        }

        // Update user info (username, email)
        $this->userModel->updateUserInfo($userId, $username, $email);

        // Update profile info (bio, image, privacy)
        $this->profileModel->updateProfileInfo($userId, $bio, $profilePicture, $isPrivate);

        // Refresh session with updated info
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