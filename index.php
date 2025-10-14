<?php
// Always load sessions first (they start automatically now)
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/controllers/UserController.php';

// Create controller
$controller = new UserController();

// Determine which action to take (default: login)
$action = $_GET['action'] ?? 'login';

// Simple router
switch ($action) {
    case 'register':
        $controller->register();
        break;

    case 'login':
        $controller->login();
        break;

    case 'logout':
        $controller->logout();
        break;

    case 'home':
        // Only logged-in users can access home
        if (isset($user)) {
            include __DIR__ . '/views/User.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;

    case 'admin':
        // Only admins can access admin area
        if (isset($user) && !empty($user['is_admin'])) {
            include __DIR__ . '/views/User.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;

    default:
        $controller->login();
        break;
}
?>