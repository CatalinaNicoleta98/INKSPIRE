<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/controllers/RegisterController.php';

$route = $_GET['route'] ?? '';
$loginController = new LoginController();
$registerController = new RegisterController();

// If session or cookie is active, user stays logged in
if (!empty($_SESSION['username']) || !empty($_COOKIE['username'])) {
    $user = $_SESSION['username'] ?? $_COOKIE['username'];
    echo "<h1>Welcome, " . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . "!</h1>";
    echo '<p><a href="index.php?route=logout">Logout</a></p>';
    exit;
}

switch ($route) {
    case 'login':
        $loginController->showForm();
        break;
    case 'register':
        $registerController->showForm();
        break;
    case 'register_post':
        $registerController->registerUser();
        break;
    case 'login_post':
        $loginController->loginUser();
        break;
    case 'logout':
        $loginController->logout();
        break;
    default:
        header('Location: index.php?route=login');
}