<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/Controllers/UserController.php';
require_once __DIR__ . '/Controllers/PostController.php';
require_once __DIR__ . '/Controllers/LikeController.php';
require_once __DIR__ . '/Controllers/CommentController.php';

// Create controllers
$userController = new UserController();
$postController = new PostController();
$likeController = new LikeController();
$commentController = new CommentController();

// Get the logged-in user from the session
$user = Session::get('user');

// Determine current action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Allow guests to access Explore, Login, and Register
if (!Session::isLoggedIn() && !in_array($action, ['explore', 'login', 'register', ''])) {
    header("Location: index.php?action=login");
    exit;
}

// Default landing page: logged-in users go home, guests go to explore
if (empty($action)) {
    if (Session::isLoggedIn()) {
        header("Location: index.php?action=home");
    } else {
        header("Location: index.php?action=explore");
    }
    exit;
}

switch ($action) {
    case 'home':
        require_once __DIR__ . '/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    case 'explore':
        require_once 'Controllers/ExploreController.php';
        $controller = new ExploreController();
        $controller->index();
        break;

    case 'profile':
        require_once __DIR__ . '/Controllers/ProfileController.php';
        $controller = new ProfileController();
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $controller->view($userId);
        break;

    case 'follow':
        require_once __DIR__ . '/Controllers/ProfileController.php';
        $controller = new ProfileController();
        if (isset($_GET['user_id'])) {
            $controller->follow(intval($_GET['user_id']));
        }
        break;

    case 'unfollow':
        require_once __DIR__ . '/Controllers/ProfileController.php';
        $controller = new ProfileController();
        if (isset($_GET['user_id'])) {
            $controller->unfollow(intval($_GET['user_id']));
        }
        break;

    case 'blockUser':
        $userController->blockUser();
        break;

    case 'unblockUser':
        $userController->unblockUser();
        break;

    case 'block':
        require_once __DIR__ . '/Controllers/ProfileController.php';
        $controller = new ProfileController();
        if (isset($_GET['user_id'])) {
            $controller->block(intval($_GET['user_id']));
        }
        break;

    case 'unblock':
        require_once __DIR__ . '/Controllers/ProfileController.php';
        $controller = new ProfileController();
        if (isset($_GET['user_id'])) {
            $controller->unblock(intval($_GET['user_id']));
        }
        break;

    case 'settings':
        require_once __DIR__ . '/Controllers/SettingsController.php';
        $controller = new SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            include __DIR__ . '/Views/Settings.php';
        }
        break;

    case 'updateSettings':
        require_once __DIR__ . '/Controllers/SettingsController.php';
        $controller = new SettingsController();
        $controller->update();
        break;

    case 'register':
        $userController->register();
        break;

    case 'login':
        $userController->login();
        break;

    case 'logout':
        $userController->logout();
        break;

    case 'createPost':
        $postController->create();
        // After creating a post, go to Explore
        header("Location: index.php?action=explore");
        exit;

    case 'admin':
        if (isset($user) && !empty($user['is_admin'])) {
            include __DIR__ . '/Views/User.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;

    case 'viewPost':
        if (isset($_GET['id'])) {
            $postController->view($_GET['id']);
        }
        break;

    // post management: edit, delete, change privacy
    case 'editPost':
        $postController->editPost();
        break;

    case 'deletePost':
        $postController->deletePost();
        break;

    case 'changePrivacy':
        $postController->changePrivacy();
        break;
    
    case 'toggleLike':
        $likeController->toggle();
        break;

    case 'addComment':
        $commentController->addComment();
        break;

    case 'getCommentsByPost':
        if (isset($_GET['post_id'])) {
            $commentController->getCommentsByPost($_GET['post_id']);
        }
        break;

    case 'deleteComment':
        $commentController->deleteComment();
        break;

    // edit existing comment
    case 'editComment':
        $commentController->editComment();
        break;

    default:
        if (isset($_SESSION['user']['user_id'])) {
            header("Location: index.php?action=home");
        } else {
            header("Location: index.php?action=explore");
        }
        break;
}
?>