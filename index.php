<?php

// Always load sessions first (they start automatically now)
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/PostController.php';

// Create controllers
$userController = new UserController();
$postController = new PostController();

$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'register':
        $userController->register();
        break;

    case 'login':
        $userController->login();
        break;

    case 'logout':
        $userController->logout();
        break;

    case 'feed':
        $postController->index();
        break;

    case 'createPost':
        $postController->create();
        break;

    case 'admin':
        if (isset($user) && !empty($user['is_admin'])) {
            include __DIR__ . '/views/User.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;

        case 'viewPost':
    if (isset($_GET['id'])) {
        $postController->view($_GET['id']);
    }
    break;

    default:
        if (isset($user)) {
            if (!empty($user['is_admin'])) {
                header("Location: index.php?action=admin");
            } else {
                header("Location: index.php?action=feed");
            }
        } else {
            $userController->login();
        }
        break;
}
?>