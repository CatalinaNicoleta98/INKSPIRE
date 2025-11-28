<?php
class LikeController {
    private $likeModel;

    public function __construct($db) {
        $this->likeModel = new LikeModel($db);
    }

    public function toggle() {
        Session::start();
        $user = Session::get('user');

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        // Prevent likes from admin-blocked users
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'You have been blocked by an administrator and cannot like posts.'
            ]);
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