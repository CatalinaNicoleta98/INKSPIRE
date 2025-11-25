<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../helpers/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../helpers/PHPMailer/SMTP.php';
require_once __DIR__ . '/../helpers/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

        $result = $this->userModel->setResetToken($email, $token, $expires);

        if ($result === "existing_token") {
            Session::set('error', 'A password reset link has already been sent. Please check your email.');
            header("Location: index.php?action=forgotPassword");
            exit;
        }

        if ($result) {
            $baseUrl = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
                ? "http://localhost/INKSPIRE"
                : "https://catalinavrinceanu.com";

            $resetLink = $baseUrl . "/index.php?action=resetPassword&token=" . $token;

            try {
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host       = 'websmtp.simply.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'inkspire@catalinavrinceanu.com';
                $mail->Password   = 'password_placeholder'; //add real password here in live
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('inkspire@catalinavrinceanu.com', 'Inkspire');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Inkspire - Password Reset Request';
                $mail->Body    = "
                    <p>Hello,</p>
                    <p>You requested a password reset. Click the link below to choose a new password:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <p>If you did not request this, please ignore this email.</p>
                ";

                $mail->send();

                Session::set('success', 'A password reset email has been sent.');
            } catch (Exception $e) {
                Session::set('error', 'Mailer Error: ' . $mail->ErrorInfo);
            }

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