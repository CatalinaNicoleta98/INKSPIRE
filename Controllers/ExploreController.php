<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/PostModel.php';
require_once __DIR__ . '/../Models/BlockModel.php';
require_once __DIR__ . '/../Models/CommentModel.php';

class ExploreController {
    private $postModel;
    private $blockModel;

    public function __construct() {
        $this->postModel = new PostModel();
        $this->blockModel = new BlockModel();
    }

    public function index() {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $user = Session::get('user');
        $userId = $user['user_id'];

        // Get list of blocked user IDs
        $blockedIds = $this->blockModel->getBlockedUsers($userId);

        // Fetch all public posts excluding logged-in user and blocked users
        $posts = $this->postModel->getAllPublicPosts($userId, $blockedIds);

        // Load comments for each post
        $commentModel = new CommentModel();
        foreach ($posts as &$post) {
            $post['comments'] = $commentModel->getCommentsByPost($post['post_id']);
            $post['comment_count'] = $commentModel->countCommentsByPost($post['post_id']); // includes replies
        }
        unset($post);

        include __DIR__ . '/../Views/Explore.php';
    }
}