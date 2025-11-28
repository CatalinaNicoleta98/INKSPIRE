<?php

class ExploreController {
    private $postModel;
    private $blockModel;

    public function __construct($db) {
        $this->postModel = new PostModel($db);
        $this->blockModel = new BlockModel($db);
    }

    public function index() {
        Session::start();
        // if (!Session::isLoggedIn()) {
        //     header('Location: index.php?action=login');
        //     exit();
        // }

        $user = Session::get('user');
        $userId = $user['user_id'] ?? null;

        if ($userId) {
            $allPosts = $this->postModel->getAllPublicPosts($userId);
            // Filter out posts from globally blocked (inactive) users
            $allPosts = array_filter($allPosts, function ($post) {
                return !isset($post['is_active']) || (int)$post['is_active'] === 1;
            });
            $posts = [];
            foreach ($allPosts as $post) {
                $postUserId = $post['user_id'];
                if (!$this->blockModel->isEitherBlocked($userId, $postUserId)) {
                    $posts[] = $post;
                }
            }
        } else {
            $posts = $this->postModel->getAllPublicPosts(null);
            // Filter out posts from globally blocked (inactive) users
            $posts = array_filter($posts, function ($post) {
                return !isset($post['is_active']) || (int)$post['is_active'] === 1;
            });
        }

        // Attach like information for each post (current user's like state)
        $likeModel = new LikeModel($this->postModel->getDb());

        if ($userId) {
            $likedPosts = $likeModel->getUserLikes($userId);
            $likedSet = array_flip($likedPosts);

            foreach ($posts as &$post) {
                $post['likes'] = $likeModel->countLikes($post['post_id']);
                $post['liked'] = isset($likedSet[$post['post_id']]);
            }
            unset($post);
        } else {
            foreach ($posts as &$post) {
                $post['likes'] = $likeModel->countLikes($post['post_id']);
                $post['liked'] = false;
            }
            unset($post);
        }

        // Load comments for each post
        $commentModel = new CommentModel($this->postModel->getDb());
        foreach ($posts as &$post) {
            $post['comments'] = $commentModel->getCommentsByPost($post['post_id']);
            $post['comment_count'] = $commentModel->countCommentsByPost($post['post_id']); // includes replies
        }
        unset($post);

        include __DIR__ . '/../Views/Explore.php';
    }
}