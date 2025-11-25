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
                $mail->Subject = 'Inkspire - Reset Your Password';

                $logoUrl = "https://catalinavrinceanu.com/uploads/logo.png";

                $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>Reset your Inkspire password</title>
</head>
<body style=\"margin:0; padding:0; background-color:#FFF7F4;\">
  <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background-color:#FFF7F4; padding:20px 0;'>
    <tr>
      <td align='center'>
        <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='max-width:520px; background:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 8px 20px rgba(0,0,0,0.06);'>

          <tr>
            <td align='center' style='padding:24px; background:linear-gradient(135deg,#FBE4DA,#F8D2D0);'>
              <img src='$logoUrl' alt='Inkspire' style='max-width:160px; height:auto; display:block; margin-bottom:8px;' />
            </td>
          </tr>

          <tr>
            <td style='padding:24px; font-family:Arial, sans-serif; color:#4A2C2F;'>
              <h2 style='margin:0 0 12px; font-size:22px; font-weight:600; color:#4A2C2F;'>Reset Your Password</h2>
              <p style='font-size:15px; line-height:1.6; margin-bottom:20px;'>
                You requested a password reset for your Inkspire account. Click the button below to create a new password.
              </p>

              <div style='text-align:center; margin:30px 0;'>
                <a href='$resetLink'
                  style='background-color:#C37B7F; color:#FFFFFF; padding:12px 22px; border-radius:8px; text-decoration:none; font-size:16px; font-weight:600; display:inline-block;'>
                  Reset Password
                </a>
              </div>

              <p style='font-size:14px; line-height:1.5; color:#6A4B4E;'>
                If the button above doesn't work, copy and paste this link into your browser:<br>
                <a href='$resetLink' style='color:#C37B7F;'>$resetLink</a>
              </p>

              <p style='font-size:13px; color:#8B6E70; margin-top:30px;'>
                If you did not request a password reset, you can safely ignore this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
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