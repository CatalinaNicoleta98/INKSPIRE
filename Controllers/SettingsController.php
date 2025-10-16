

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

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $bio = trim($_POST['bio']);

        // Handle profile picture upload
        $profilePicture = $user['profile_picture'] ?? null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $targetDir = __DIR__ . '/../uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicture = 'uploads/' . $fileName;
            }
        }

        // Update user table
        $this->userModel->updateUserInfo($userId, $username, $email);

        // Update profile table
        $this->profileModel->updateProfileInfo($userId, $bio, $profilePicture);

        // Refresh session data
        $updatedUser = $this->userModel->getUserById($userId);
        $updatedUser['bio'] = $bio;
        $updatedUser['profile_picture'] = $profilePicture;
        Session::set('user', $updatedUser);

        header('Location: index.php?action=settings&success=1');
        exit();
    }
}
?>