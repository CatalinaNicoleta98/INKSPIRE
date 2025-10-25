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
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $post_id = $_POST['post_id'] ?? null;
        $content = $_POST['text'] ?? '';

        if ($post_id && !empty(trim($content))) {
            $user_id = $user['user_id'];
            $success = $this->model->addComment($post_id, $user_id, $content);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
        }
    }

    // Get comments for a specific post
    public function getCommentsByPost($post_id) {
        global $user;

        $comments = $this->model->getCommentsByPost($post_id);

        // mark which comments belong to the logged in user
        if (isset($user)) {
            foreach ($comments as &$comment) {
                $comment['owned'] = ($comment['user_id'] == $user['user_id']);
            }
        } else {
            foreach ($comments as &$comment) {
                $comment['owned'] = false;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($comments);
    }

    // Delete a comment
    public function deleteComment() {
        global $user;

        if (!isset($user)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'] ?? null;

        if ($comment_id) {
            $user_id = $user['user_id'];
            $success = $this->model->deleteComment($comment_id, $user_id);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        }
    }

    // edit an existing comment if it's owned by the user
    public function editComment() {
        global $user;

        if (!isset($user)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'] ?? null;
        $text = $_POST['text'] ?? '';

        if ($comment_id && !empty(trim($text))) {
            $user_id = $user['user_id'];
            $success = $this->model->updateComment($comment_id, $user_id, $text);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
        }
    }
}
?>