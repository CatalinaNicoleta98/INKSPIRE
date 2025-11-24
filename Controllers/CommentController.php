<?php
require_once __DIR__ . '/../Models/CommentModel.php';
require_once __DIR__ . '/../helpers/Session.php';

class CommentController {
    private $model;

    public function __construct() {
        $this->model = new CommentModel();
    }

    // Add a comment (supports replies)
    public function addComment() {
        Session::start();
        $user = Session::get('user');

        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        // Prevent comments from admin-blocked users
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'You have been blocked by an administrator and cannot comment.'
            ]);
            exit;
        }

        $post_id = $_POST['post_id'] ?? null;
        $content = $_POST['text'] ?? '';
        $parent_id = $_POST['parent_id'] ?? null;

        if ($post_id && !empty(trim($content))) {
            $user_id = $user['user_id'];
            $success = $this->model->addComment($post_id, $user_id, $content, $parent_id);
            if ($success) {
                // Always count all comments including replies
                $totalCount = $this->model->countCommentsByPost($post_id);
                $username = $user['username'] ?? 'Unknown';
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'count' => $totalCount,
                    'comment' => [
                        'comment_id' => (int)$success,
                        'post_id'    => (int)$post_id,
                        'parent_id'  => $parent_id ? (int)$parent_id : null,
                        'username'   => $username,
                        'created_at' => date('M j, Y H:i'),
                        'text'       => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
                        'owned'      => true
                    ]
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false]);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
    }

    // Get comments for a specific post (including replies and ownership)
    public function getCommentsByPost($post_id) {
        Session::start();
        $user = Session::get('user');
        $current_user_id = $user ? $user['user_id'] : 0;

        $comments = $this->model->getCommentsByPost($post_id, $current_user_id);

        header('Content-Type: application/json');
        echo json_encode($comments);
    }

    // Delete a comment
    public function deleteComment() {
        Session::start();
        $user = Session::get('user');

        // Block globally blocked users
        if (isset($user['is_globally_blocked']) && (int)$user['is_globally_blocked'] === 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You are blocked and cannot delete comments.']);
            exit;
        }

        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'] ?? null;

        if ($comment_id) {
            $user_id = $user['user_id'];

            // Determine if user is admin (support multiple patterns)
            $isAdmin = false;

            if (!empty($user['role'])) {
                $role = strtolower(trim($user['role']));
                if (in_array($role, ['admin', 'administrator', 'superadmin'])) {
                    $isAdmin = true;
                }
            }

            if (isset($user['is_admin']) && (int)$user['is_admin'] === 1) {
                $isAdmin = true;
            }
            if (isset($user['isAdmin']) && $user['isAdmin']) {
                $isAdmin = true;
            }

            // Retrieve post_id if not passed (for replies)
            $post_id = $_POST['post_id'] ?? $this->model->getPostIdByComment($comment_id);

            // First try: delete as the comment owner
            $success = $this->model->deleteComment($comment_id, $user_id);

            // If that failed and the user is admin, allow admin override delete
            if (!$success && $isAdmin) {
                $success = $this->model->deleteComment($comment_id, null);
            }

            header('Content-Type: application/json');
            if ($success) {
                if ($post_id) {
                    $totalCount = $this->model->countCommentsByPost($post_id);
                    echo json_encode(['success' => true, 'count' => $totalCount]);
                } else {
                    echo json_encode(['success' => true]);
                }
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        }
    }

    // edit an existing comment if it's owned by the user
    public function editComment() {
        global $user;

        // Block globally blocked users
        if (isset($user['is_globally_blocked']) && (int)$user['is_globally_blocked'] === 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You are blocked and cannot edit comments.']);
            exit;
        }

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