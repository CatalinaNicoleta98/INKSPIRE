<?php

class HomeController {
    private $postModel;
    private $profileModel;
    private $blockModel;

    public function __construct($db) {
        $this->postModel = new PostModel($db);
        $this->profileModel = new ProfileModel($db);
        $this->blockModel = new BlockModel($db);
    }

    public function index() {
        Session::start();
        if (!Session::isLoggedIn()) {
            header('Location: index.php?action=login');
            exit();
        }

        $user = Session::get('user');
        // Ensure session user is a valid array
        if (!is_array($user)) {
            // Force logout or redirect to login if session is corrupted
            header('Location: index.php?action=login');
            exit();
        }

        $userId = $user['user_id'];

        // If the user is admin-blocked (is_active = 0), show no posts
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            $posts = [];
            $isAdminBlocked = true; // Pass to view
            include __DIR__ . '/../Views/Home.php';
            return;
        }

        // Fetch posts from followed users
        $posts = $this->postModel->getPostsFromFollowedUsers($userId);

        // Filter out posts from globally blocked users (inactive accounts)
        $posts = array_filter($posts, function ($post) {
            return !isset($post['is_active']) || (int)$post['is_active'] === 1;
        });

        // Also fetch the logged‑in user's own posts
        if (method_exists($this->postModel, 'getPostsByUser')) {
            $selfPostsAll = $this->postModel->getPostsByUser($userId);
            $selfPosts = [];
            if (!empty($selfPostsAll)) {
                // Only keep latest post by logged‑in user
                usort($selfPostsAll, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                $selfPosts = [$selfPostsAll[0]];
            }
            // Merge: latest self post first, then followed users
            $posts = array_merge($selfPosts, $posts);
        }

        // Filter out posts from blocked or blocking users
        $posts = array_filter($posts, function ($post) use ($userId) {
            return !$this->blockModel->isEitherBlocked($userId, $post['user_id']);
        });

        // Attach like information for each post (current user's like state)
        $likeModel = new LikeModel($this->postModel->getDb());
        $likedPosts = $likeModel->getUserLikes($userId);
        $likedSet = array_flip($likedPosts);

        foreach ($posts as &$post) {
            $post['likes'] = $likeModel->countLikes($post['post_id']);
            $post['liked'] = isset($likedSet[$post['post_id']]);
        }
        unset($post);

        // Load comments for each post
        $commentModel = new CommentModel($this->postModel->getDb());

        foreach ($posts as &$post) {
            $post['comments'] = $commentModel->getCommentsByPost($post['post_id']);
            $post['comment_count'] = $commentModel->countCommentsByPost($post['post_id']); // includes replies
        }
        unset($post);

        include __DIR__ . '/../Views/Home.php';
    }
}