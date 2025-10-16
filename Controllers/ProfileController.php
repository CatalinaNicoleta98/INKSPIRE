

<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/../Models/PostModel.php';

class ProfileController {
    private $profileModel;
    private $postModel;

    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->postModel = new PostModel();
    }

    public function view($userId = null) {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $currentUser = Session::get('user');
        if ($userId === null) {
            $userId = $currentUser['user_id'];
        }

        $profile = $this->profileModel->getUserProfile($userId);

        if (!$profile) {
            $profile = [
                'username' => $currentUser['username'] ?? 'Unknown User',
                'bio' => '',
                'profile_picture' => 'uploads/default.png',
                'followers' => 0,
                'following' => 0
            ];
        }

        $posts = $this->postModel->getPostsByUser($userId);

        include __DIR__ . '/../Views/Profile.php';
    }
}
?>