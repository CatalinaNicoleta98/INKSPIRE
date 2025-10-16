<?php
require_once __DIR__ . '/../Models/CommentModel.php';
require_once __DIR__ . '/../helpers/Session.php';

class CommentController {
    private $model;

    public function __construct() {
        $this->model = new CommentModel();
    }

    // Add a comment
    public function addComment() {
        global $user;

        if (!isset($user)) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $post_id = $_POST['post_id'] ?? null;
        $content = $_POST['content'] ?? '';

        if ($post_id && !empty(trim($content))) {
            $user_id = $user['user_id'];
            $success = $this->model->addComment($post_id, $user_id, $content);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
        }
    }

    // Get comments for a specific post
    public function getCommentsByPost($post_id) {
        $comments = $this->model->getCommentsByPost($post_id);
        header('Content-Type: application/json');
        echo json_encode($comments);
    }
}
?>