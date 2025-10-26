<?php
require_once __DIR__ . "/../Models/PostModel.php";
require_once __DIR__ . "/../helpers/Session.php";


class PostController {
    private $postModel;

    public function __construct() {
        $this->postModel = new PostModel();
    }

    // Show all posts (feed)
    public function index() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header("Location: index.php?action=login");
            exit;
        }

        require_once __DIR__ . '/../Models/LikeModel.php';
        $likeModel = new LikeModel();

        $posts = $this->postModel->getAllPosts();

        // Get liked posts for current user
        $likedPosts = $likeModel->getUserLikes($user['user_id']);
        $likedSet = array_flip($likedPosts); // for quick lookup

        // Add like info to each post
        foreach ($posts as &$post) {
            $post['likes'] = $likeModel->countLikes($post['post_id']);
            $post['liked'] = isset($likedSet[$post['post_id']]);
        }

        // Load comments for each post
        require_once __DIR__ . '/../Models/CommentModel.php';
        $commentModel = new CommentModel();
        // Fetch comments for each post
        foreach ($posts as &$post) {
            $post['comments'] = $commentModel->getCommentsByPost($post['post_id']);
        }

        include __DIR__ . '/../Views/Post.php';
    }

    public function view($postId) {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header("Location: index.php?action=login");
            exit;
        }

        $post = $this->postModel->getPostById($postId);

        require_once __DIR__ . '/../Models/LikeModel.php';
        $likeModel = new LikeModel();
        $post['likes'] = $likeModel->countLikes($postId);
        $post['liked'] = $likeModel->userLiked($user['user_id'], $postId);

        header('Content-Type: application/json');
        echo json_encode($post);
        exit;
    }

    // user edits a post
    public function editPost() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $postId = $_POST['post_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        if (!$postId || empty(trim($title)) || empty(trim($description))) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        $success = $this->postModel->updatePost($postId, $user['user_id'], $title, $description);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    // user deletes their post
    public function deletePost() {
        // Ensure session is active and user is loaded
        Session::start();
        $user = Session::get('user');

        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $postId = $_POST['post_id'] ?? null;
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'Invalid post']);
            exit;
        }

        $success = $this->postModel->deletePost($postId, $user['user_id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    // user toggles privacy
    public function changePrivacy() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $postId = $_POST['post_id'] ?? null;
        $isPublic = $_POST['is_public'] ?? null;

        if ($postId === null || $isPublic === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        $isPublic = (int)$isPublic;
        $success = $this->postModel->togglePrivacy($postId, $user['user_id'], $isPublic);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }
    // Handle new post submission
    public function create() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            if (empty($title) || empty($description)) {
                echo "All fields are required.";
                exit;
            }

            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $this->resizeImage($targetFile);
                    $imagePath = 'uploads/' . $fileName;
                } else {
                    echo "Image upload failed.";
                    exit;
                }
            }

            $success = $this->postModel->createPost($title, $description, $imagePath, $user['user_id'], $tags);

            if ($success) {
                header("Location: index.php?action=feed");
                exit;
            } else {
                echo "Database error — check debug_sql.txt";
            }
        } else {
            header("Location: index.php?action=feed");
            exit;
        }
    }

    private function resizeImage($filePath, $maxWidth = 1200, $maxHeight = 1200) {
        list($width, $height, $type) = getimagesize($filePath);
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        $ratio = $width / $height;
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($filePath);
                break;
            default:
                return;
        }

        $dst = imagecreatetruecolor($maxWidth, $maxHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst, $filePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst, $filePath);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }
}
?>