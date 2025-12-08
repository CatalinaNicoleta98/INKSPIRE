<?php

class SearchController {
    private $searchModel;
    private $likeModel;
    private $commentModel;

    public function __construct($db) {
        Session::start();
        $this->searchModel = new SearchModel($db);
        $this->likeModel = new LikeModel($db);
        $this->commentModel = new CommentModel($db);
    }

    // JSON suggestions for the dropdown
    public function suggestions() {
        header('Content-Type: application/json');

        $query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($query === '') {
            echo json_encode([
                'users' => [],
                'posts' => [],
                'tags'  => []
            ]);
            return;
        }

        $users = $this->searchModel->searchUsers($query, 5);
        $tags  = $this->searchModel->searchTags($query, 5);

        echo json_encode([
            'users' => $users,
            'tags'  => $tags,
            'posts' => [] // kept for compatibility
        ]);
    }

    // Full results search
    public function results() {
        Session::start();

        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $type  = isset($_GET['type']) ? $_GET['type'] : 'profiles';

        $users = $this->searchModel->searchUsers($query, 50);
        $tags  = $this->searchModel->searchTags($query, 50);
        $posts = [];

        if ($type === 'tags') {
            $rawPosts = $this->searchModel->searchPostsByTag($query, 50);
            $posts = [];

            $currentUserId = $_SESSION['user']['user_id'] ?? null;

            foreach ($rawPosts as $post) {
                $post['likes'] = $this->likeModel->countLikes($post['post_id']);
                $post['liked'] = $currentUserId ? $this->likeModel->userLiked($currentUserId, $post['post_id']) : false;
                $post['comment_count'] = $this->commentModel->countCommentsByPost($post['post_id']);
                $posts[] = $post;
            }
        }

        $activeType = $type;

        // Sidebar variables
        $db = $this->searchModel->getDb();
        $sidebar = SidebarController::data($db);
        extract($sidebar);

        // Header variables
        $loggedInUser = $_SESSION['user'] ?? null;
        $header = HeaderController::data($db, $loggedInUser);
        extract($header);

        include __DIR__ . '/../Views/SearchResults.php';
    }
}
