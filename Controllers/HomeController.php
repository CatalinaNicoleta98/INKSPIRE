<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/PostModel.php';
require_once __DIR__ . '/../Models/ProfileModel.php';

class HomeController {
    private $postModel;
    private $profileModel;

    public function __construct() {
        $this->postModel = new PostModel();
        $this->profileModel = new ProfileModel();
    }

    // The Home page now only displays posts from followed users and the current user.
    // Post creation functionality is handled separately by PostController.
    public function index() {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $user = Session::get('user');
        $userId = $user['user_id'];

        // Get the IDs of profiles the user follows
        $following = $this->profileModel->getFollowingIds($userId);

        // Include the user's own posts
        $ids = array_merge([$userId], $following);

        // Fetch posts for home feed
        $posts = $this->postModel->getPostsForUsers($ids);

        include __DIR__ . '/../Views/Home.php';
    }
}