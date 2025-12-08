<?php

class PostController {
    private $postModel;

    public function __construct($db) {
        $this->postModel = new PostModel($db);
    }

    // Show all posts (feed)
    public function index() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header("Location: index.php?action=login");
            exit;
        }

        $likeModel = new LikeModel($this->postModel->getDb());

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
        $commentModel = new CommentModel($this->postModel->getDb());
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

        $likeModel = new LikeModel($this->postModel->getDb());
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
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $removeImage = isset($_POST['remove_image']);

        if (!$postId || empty($title) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        // Fetch existing post to know current image
        $existing = $this->postModel->getPostById($postId);
        if (!$existing) {
            echo json_encode(['success' => false, 'message' => 'Post not found']);
            exit;
        }
        $currentImage = $existing['image_url'] ?? null;

        // Handle image logic
        $newImagePath = $currentImage;

        // Remove image
        if ($removeImage && $currentImage) {
            $file = __DIR__ . '/../' . $currentImage;
            if (file_exists($file)) unlink($file);
            $newImagePath = null;
        }

        // Replace with new upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmp = $_FILES['image']['tmp_name'];
            $mime = mime_content_type($fileTmp);
            $allowed = ['image/jpeg','image/png','image/gif'];

            if (!in_array($mime, $allowed) || !@getimagesize($fileTmp)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image']);
                exit;
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $target = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $target)) {
                ImageResizer::resizeImage($target);
                $newImagePath = 'uploads/' . $fileName;

                if ($currentImage && !$removeImage) {
                    $old = __DIR__ . '/../' . $currentImage;
                    if (file_exists($old)) unlink($old);
                }
            }
        }

        // Update DB
        $success = $this->postModel->updatePostFull($postId, $user['user_id'], $title, $description, $newImagePath, $tags);

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

        // Fetch post to know current image before deletion
        $post = $this->postModel->getPostById($postId);
        $imageToDelete = $post['image_url'] ?? null;

        // Determine if user is admin (support multiple patterns)
        $isAdmin = false;

        // Check common "role" patterns
        if (!empty($user['role'])) {
            $role = strtolower(trim($user['role']));
            if (in_array($role, ['admin', 'administrator', 'superadmin'])) {
                $isAdmin = true;
            }
        }

        // Check common boolean admin flags
        if (isset($user['is_admin']) && (int)$user['is_admin'] === 1) {
            $isAdmin = true;
        }
        if (isset($user['isAdmin']) && $user['isAdmin']) {
            $isAdmin = true;
        }

        // First try: user can delete their own post
        $success = $this->postModel->deletePost($postId, $user['user_id']);

        // If that failed and user is admin, allow admin override delete
        if (!$success && $isAdmin) {
            $success = $this->postModel->deletePost($postId, null);
        }

        // If delete succeeded and there was an image, remove the file
        if ($success && $imageToDelete) {
            $file = __DIR__ . '/../' . $imageToDelete;
            if (file_exists($file)) {
                unlink($file);
            }
        }

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
        // Prevent admin-blocked users from creating posts
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'You have been blocked by an administrator and cannot create posts.'
            ]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF token validation
            Session::start();
            $sessionToken = $_SESSION['post_token'] ?? null;
            $formToken = $_POST['token'] ?? null;

            if (!$sessionToken || !$formToken || !hash_equals($sessionToken, $formToken)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => '⚠️ Invalid or expired submission token.']);
                exit;
            }

            // Consume token so it cannot be reused
            unset($_SESSION['post_token']);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            if (empty($title) || empty($description)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => '⚠️ Title and description are required.']);
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

                // Validate MIME and real image content
                $fileTmp = $_FILES['image']['tmp_name'];
                $fileMime = mime_content_type($fileTmp);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($fileMime, $allowedTypes)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => '⚠️ Invalid file type. Please upload only PNG, JPEG, or GIF images.']);
                    exit;
                }

                // Verify actual image structure (blocks renamed non-images)
                if (!@getimagesize($fileTmp)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => '⚠️ The uploaded file is not a valid image or is corrupted.']);
                    exit;
                }

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    // Resize and sanitize
                    ImageResizer::resizeImage($targetFile);
                    $imagePath = 'uploads/' . $fileName;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => '⚠️ Image upload failed. Please try again.']);
                    exit;
                }
            }

            $success = $this->postModel->createPost($title, $description, $imagePath, $user['user_id'], $tags);

            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => '⚠️ Database error — please try again.']);
            }
            exit;
        } else {
            header("Location: index.php?action=feed");
            exit;
        }
    }

    // Full-page single post view (used by notifications)
    public function showPostPage() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header("Location: index.php?action=login");
            exit;
        }

        $postId = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$postId) {
            header("Location: index.php?action=home");
            exit;
        }

        // Fetch the post with full details
        $post = $this->postModel->getPostById($postId);
        if (!$post) {
            header("Location: index.php?action=notifications");
            exit;
        }

        // Likes
        $likeModel = new LikeModel($this->postModel->getDb());
        $post['likes'] = $likeModel->countLikes($postId);
        $post['liked'] = $likeModel->userLiked($user['user_id'], $postId);

        // Comments
        $commentModel = new CommentModel($this->postModel->getDb());
        $post['comments'] = $commentModel->getCommentsByPost($postId);

        // Comment highlight (from notifications)
        $highlightCommentId = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : null;
        if ($highlightCommentId) {
            $commentModelCheck = new CommentModel($this->postModel->getDb());
            $commentCheck = $commentModelCheck->getCommentById($highlightCommentId);
            if (!$commentCheck || intval($commentCheck['post_id']) !== intval($postId)) {
                // Invalid or unrelated comment → show 404
                include __DIR__ . '/../Views/404.php';
                exit;
            }
        }

        // Prepare data exactly as Post.php expects
        $posts = [$post];

        // Sidebar variables
        $db = $this->postModel->getDb();
        $sidebar = SidebarController::data($db);
        extract($sidebar);

        include __DIR__ . '/../Views/templates/SinglePost.php';
    }

    // Toggle sticky / pinned state for a post
    public function toggleSticky() {
        Session::start();
        $user = Session::get('user');
        if (!$user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $postId = $_POST['post_id'] ?? null;
        if (!$postId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
            exit;
        }

        // Fetch post to ensure ownership
        $post = $this->postModel->getPostById($postId);
        if (!$post || $post['user_id'] != $user['user_id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Determine the new sticky state
        $newSticky = ($post['is_sticky'] == 1) ? 0 : 1;

        // Optional: enforce a maximum number of pinned posts
        // Example: limit to 3 pinned posts
        if ($newSticky == 1) {
            $countSticky = $this->postModel->countStickyPosts($user['user_id']);
            if ($countSticky >= 3) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You can only pin up to 3 posts.']);
                exit;
            }
        }

        $success = $this->postModel->setSticky($postId, $newSticky);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'sticky' => $newSticky]);
        exit;
    }
}

?>