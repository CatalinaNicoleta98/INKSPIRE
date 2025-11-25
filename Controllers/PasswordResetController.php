<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Helpers/Session.php';

class PasswordResetController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function showForgotPasswordForm()
    {
        $action = 'forgotPassword';
        require __DIR__ . '/../Views/User.php';
    }

    public function sendResetLink()
    {
        if (!isset($_POST['email'])) {
            Session::set('error', 'Please enter your email.');
            header("Location: index.php?action=forgotPassword");
            exit;
        }

        $email = trim($_POST['email']);
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($this->userModel->setResetToken($email, $token, $expires)) {
            $resetLink = "http://localhost/INKSPIRE/index.php?action=resetPassword&token=" . $token;

            // For now, show link on screen. Later we will send email.
            Session::set('success', "Password reset link: " . $resetLink);
            header("Location: index.php?action=forgotPassword");
            exit;
        }

        Session::set('error', 'Email not found.');
        header("Location: index.php?action=forgotPassword");
        exit;
    }

    public function showResetForm()
    {
        if (!isset($_GET['token'])) {
            echo "Invalid token.";
            return;
        }

        $token = $_GET['token'];
        $user = $this->userModel->findUserByResetToken($token);

        if (!$user) {
            echo "Invalid or expired reset link.";
            return;
        }

        $action = 'resetPassword';
        require __DIR__ . '/../Views/User.php';
    }

    public function resetPassword()
    {
        if (!isset($_POST['token']) || !isset($_POST['password'])) {
            echo "Invalid request.";
            return;
        }

        $token = $_POST['token'];
        $password = $_POST['password'];

        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Check if passwords match
        if ($password !== $passwordConfirm) {
            Session::set('error', 'Passwords do not match.');
            header("Location: index.php?action=resetPassword&token=" . $token);
            exit;
        }

        // Password strength validation
        $valid = preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password);

        if (!$valid) {
            Session::set('error', 'Password does not meet the security requirements.');
            header("Location: index.php?action=resetPassword&token=" . $token);
            exit;
        }

        $user = $this->userModel->findUserByResetToken($token);

        if (!$user) {
            echo "Invalid or expired reset link.";
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($this->userModel->updatePasswordByToken($token, $hashed)) {
            $this->userModel->clearResetToken($token);
            Session::set('success', 'Password updated successfully. You can now log in.');
            header("Location: index.php?action=login");
            exit;
        }

        echo "An error occurred while updating password.";
    }
}
?>