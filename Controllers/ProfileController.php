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
        $viewerId = $currentUser['user_id'];

        // Determine which profile to show
        if ($userId === null) {
            $userId = $viewerId;
        }

        // Get profile info
        $profile = $this->profileModel->getUserProfile($userId);
        if (!$profile) {
            $profile = [
                'username' => 'Unknown User',
                'bio' => '',
                'profile_picture' => 'uploads/default.png',
                'followers' => 0,
                'following' => 0,
                'is_private' => 0
            ];
        }

        // Check if user can view this profile's posts
        $canViewPosts = false;
        if ($userId == $viewerId) {
            $canViewPosts = true;
        } elseif ($profile['is_private'] == 0) {
            $canViewPosts = true;
        } else {
            // TODO: integrate follow check later
            $canViewPosts = false;
        }

        // Load posts conditionally
        $posts = $canViewPosts ? $this->postModel->getPostsByUser($userId) : [];

        include __DIR__ . '/../Views/Profile.php';
    }
}
?>