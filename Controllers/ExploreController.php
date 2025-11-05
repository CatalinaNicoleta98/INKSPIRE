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

        // Fetch all public posts excluding posts from blocked or blocking users
        $allPosts = $this->postModel->getAllPublicPosts($userId);
        $posts = [];
        foreach ($allPosts as $post) {
            $postUserId = $post['user_id'];
            if (!$this->blockModel->isEitherBlocked($userId, $postUserId)) {
                $posts[] = $post;
            }
        }

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