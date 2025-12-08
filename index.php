<?php
require_once __DIR__ . '/autoloader.php';
header('Content-Type: text/html; charset=utf-8');

$database = new Database();
$db = $database->connect();

// Create controllers
$userController = new UserController($db);
$postController = new PostController($db);
$likeController = new LikeController($db);
$commentController = new CommentController($db);
$searchController = new SearchController($db);
$passwordResetController = new PasswordResetController($db);

// Get the logged-in user from the session
$user = Session::get('user');

// Determine current action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Allow guests to access Explore, Login, and Register
if (!Session::isLoggedIn() && !in_array($action, ['explore', 'login', 'register', 'notifications', 'viewNotification', 'forgotPassword', 'sendResetLink', 'resetPassword', 'updatePassword', ''])) {
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
    case 'searchSuggestions':
        $searchController->suggestions();
        break;

    case 'search':
        $searchController->results();
        break;
    case 'home':
        $controller = new HomeController($db);
        $controller->index();
        break;

    case 'explore':
        $controller = new ExploreController($db);
        $controller->index();
        break;

    case 'profile':
        $controller = new ProfileController($db);
        $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $controller->view($userId);
        break;

    case 'follow':
        $controller = new ProfileController($db);
        if (isset($_GET['user_id'])) {
            $controller->follow(intval($_GET['user_id']));
        }
        break;

    case 'unfollow':
        $controller = new ProfileController($db);
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
        $controller = new ProfileController($db);
        if (isset($_GET['user_id'])) {
            $controller->block(intval($_GET['user_id']));
        }
        break;

    case 'unblock':
        $controller = new ProfileController($db);
        if (isset($_GET['user_id'])) {
            $controller->unblock(intval($_GET['user_id']));
        }
        break;

    case 'settings':
        $controller = new SettingsController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->show();
        }
        break;

    case 'updateSettings':
        $controller = new SettingsController($db);
        $controller->update();
        break;

    case 'deleteProfilePicture':
        $controller = new SettingsController($db);
        $controller->deleteProfilePicture();
        break;

    case 'deleteAccount':
        $controller = new SettingsController($db);
        $controller->deleteAccount();
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

    case 'getSuggestedUsers':
        $userController->getSuggestedUsers();
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

    case 'post':
        // Full-page single post
        $postController->showPostPage();
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

    case 'toggleSticky':
        $postController->toggleSticky();
        exit;
        break;

    case 'notifications':
        $controller = new NotificationController($db);
        $controller->index();
        break;

    case 'viewNotification':
        $controller = new NotificationController($db);
        $controller->view();
        break;

    case 'markNotificationRead':
        $controller = new NotificationController($db);
        $controller->markNotificationRead();
        exit;

    case 'markNotificationUnread':
        $controller = new NotificationController($db);
        $controller->markNotificationUnread();
        exit;

    case 'markAllNotificationsRead':
        $controller = new NotificationController($db);
        $controller->markAllNotificationsRead();
        exit;

    case 'deleteNotification':
        $controller = new NotificationController($db);
        $controller->deleteNotification();
        exit;

    case 'deleteAllNotifications':
        $controller = new NotificationController($db);
        $controller->deleteAllNotifications();
        exit;

    case 'toggleAdminView':
        $controller = new SettingsController($db);
        $controller->toggleAdminView();
        break;

    case 'adminPanel':
        $controller = new AdminController($db);
        $controller->dashboard();
        break;

    case 'adminToggleBlock':
        $controller = new AdminController($db);
        $controller->toggleBlock();
        break;

    case 'adminToggleAdmin':
        $controller = new AdminController($db);
        $controller->toggleAdmin();
        break;

    case 'forgotPassword':
        $passwordResetController->showForgotPasswordForm();
        break;

    case 'sendResetLink':
        $passwordResetController->sendResetLink();
        break;

    case 'resetPassword':
        $passwordResetController->showResetForm();
        break;

    case 'updatePassword':
        $passwordResetController->resetPassword();
        break;

    case 'updateTerms':
        $controller = new AdminController($db);
        $controller->updateTerms();
        break;

    case 'updateAbout':
        $controller = new AdminController($db);
        $controller->updateAbout();
        break;

    case 'feed':
        header("Location: index.php?action=home");
        exit;
        break;

    default:
        include __DIR__ . '/Views/404.php';
        exit;
        break;
}
?>