<?php
require_once __DIR__ . '/../models/LoginModel.php';

class LoginController {
    public function showForm(): void {
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
            $error = "Invalid username/email or password.";
            require __DIR__ . '/../views/loginView.php';
            return;
        }

        // Save login in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // Optional: keep logged in for 7 days
        setcookie('username', $user['username'], time() + (86400 * 7), "/");

        header('Location: index.php');
        exit;
    }

    public function logout(): void {
        session_unset();
        session_destroy();

        // clear cookie
        setcookie('username', '', time() - 3600, "/");

        header('Location: index.php?route=login');
        exit;
    }
}