<?php
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/helpers/Session.php';

Session::start();
$controller = new UserController();

$action = $_GET['action'] ?? 'login';

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
        if (Session::isLoggedIn()) {
            include __DIR__ . '/views/User.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;
    case 'admin':
        if (Session::isLoggedIn() && Session::get('user')['is_admin']) {
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