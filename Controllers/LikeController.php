<?php
require_once __DIR__ . '/../Models/LikeModel.php';
require_once __DIR__ . '/../helpers/Session.php';

class LikeController {
    private $likeModel;

    public function __construct() {
        $this->likeModel = new LikeModel();
    }

    public function toggle() {
        Session::start();
        $user = Session::get('user');

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $user['user_id'];
        $postId = $_POST['post_id'] ?? $_GET['post_id'] ?? null;
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'Missing post_id']);
            exit;
        }

        $liked = $this->likeModel->userLiked($userId, $postId);

        if ($liked) {
            $this->likeModel->removeLike($userId, $postId);
            $newStatus = false;
        } else {
            $this->likeModel->addLike($userId, $postId);
            $newStatus = true;
        }

        $count = $this->likeModel->countLikes($postId);

        echo json_encode([
            'success' => true,
            'liked' => $newStatus,
            'likes' => $count
        ]);
        exit;
    }
}
?>