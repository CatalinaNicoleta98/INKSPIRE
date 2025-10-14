<?php
require_once __DIR__ . '/../models/LoginModel.php';

class LoginController {
    public function showForm(): void {
        $error   = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require __DIR__ . '/../views/loginView.php';
    }

    public function loginUser(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=login');
            exit;
        }

        $usernameOrEmail = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'] ?? '';

        $model = new LoginModel();
        $user = $model->findUser($usernameOrEmail);

        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: index.php?route=login&error=Invalid+username+or+password');
            exit;
        }

        // ✅ Secure session setup
        session_regenerate_id(true); // prevents session fixation
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        header('Location: index.php');
        exit;
    }

    public function logout(): void {
        // ✅ Clear all session data
        $_SESSION = [];

        // ✅ Delete session cookie properly
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // ✅ Finally, destroy the session
        session_destroy();

        header('Location: index.php?route=login&success=You+have+been+logged+out');
        exit;
    }
}