<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/PostModel.php';
require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/../Models/BlockModel.php';

class HomeController {
    private $postModel;
    private $profileModel;
    private $blockModel;

    public function __construct() {
        $this->postModel = new PostModel();
        $this->profileModel = new ProfileModel();
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

        // Fetch posts only from followed users (not including self)
        $posts = $this->postModel->getPostsFromFollowedUsers($userId);

        // Filter out posts from blocked or blocking users
        $posts = array_filter($posts, function ($post) use ($userId) {
            $blockModel = new BlockModel();
            return !$blockModel->isEitherBlocked($userId, $post['user_id']);
        });

        // Load comments for each post
        require_once __DIR__ . '/../Models/CommentModel.php';
        $commentModel = new CommentModel();

        foreach ($posts as &$post) {
            $post['comments'] = $commentModel->getCommentsByPost($post['post_id']);
            $post['comment_count'] = $commentModel->countCommentsByPost($post['post_id']); // includes replies
        }
        unset($post);

        include __DIR__ . '/../Views/Home.php';
    }
}