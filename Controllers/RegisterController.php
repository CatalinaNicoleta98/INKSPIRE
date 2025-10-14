<?php
require_once __DIR__ . '/../models/RegisterModel.php';

class RegisterController {
    public function showForm(): void {
        require __DIR__ . '/../views/registerView.php';
    }

    public function registerUser(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=register');
            exit;
        }

        // Sanitize inputs
        $first = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $last  = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $user  = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $pass  = $_POST['password'] ?? '';
        $dob   = htmlspecialchars(trim($_POST['dob'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($first === '' || $last === '' || $email === '' || $user === '' || $pass === '') {
            $error = "All fields are required.";
            require __DIR__ . '/../views/registerView.php';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
            require __DIR__ . '/../views/registerView.php';
            return;
        }

        $model = new RegisterModel();

        if ($model->userExists($user, $email)) {
            $error = "Username or email already in use.";
            require __DIR__ . '/../views/registerView.php';
            return;
        }

        $model->createUser([
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => $email,
            'username'   => $user,
            'password'   => $pass,
            'dob'        => $dob
        ]);

        // ✅ Don’t auto-login; show confirmation instead
        $success = "Registration successful! You can now log in.";
        require __DIR__ . '/../views/loginView.php';
    }
}