<?php
require_once __DIR__ . '/../Models/SearchModel.php';
require_once __DIR__ . '/../helpers/Session.php';

class SearchController {
    private $searchModel;

    public function __construct() {
        Session::start();
        $this->searchModel = new SearchModel();
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
        $posts = [];
        $tags  = $this->searchModel->searchTags($query, 50);

        $activeType = $type;

        require_once __DIR__ . '/../Views/SearchResults.php';
    }
}
