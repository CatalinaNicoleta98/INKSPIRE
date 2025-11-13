<?php
require_once __DIR__ . "/../Models/UserModel.php";
require_once __DIR__ . "/../helpers/Session.php";
require_once __DIR__ . "/../Models/BlockModel.php";

class UserController {
    private $userModel;
    private $blockModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->blockModel = new BlockModel();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $dob = $_POST['dob'] ?? ''; // date of birth from the form

            // Confirm password
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            // Backend password match validation
            if ($password !== $passwordConfirm) {
                $error = "Passwords do not match.";
                include __DIR__ . '/../Views/User.php';
                return;
            }

            // Strong password validation
            $hasMinLength = strlen($password) >= 8;
            $hasUpper = preg_match('/[A-Z]/', $password);
            $hasLower = preg_match('/[a-z]/', $password);
            $hasNumber = preg_match('/[0-9]/', $password);
            $hasSpecial = preg_match('/[^A-Za-z0-9]/', $password);

            if (!($hasMinLength && $hasUpper && $hasLower && $hasNumber && $hasSpecial)) {
                $error = "Password must be at least 8 characters long and include upper, lower, number and special character.";
                include __DIR__ . '/../Views/User.php';
                return;
            }

            // Try to register and handle all possible outcomes (show clear messages for the user)
            $result = $this->userModel->register($firstName, $lastName, $email, $username, $password, $dob);

            if ($result === true) {
                // registration succeeded
                $_SESSION['success_message'] = "Account created successfully! Please log in.";
                header("Location: index.php?action=login");
                exit;
            } elseif ($result === "too_young") {
                $error = "You must be at least 14 years old to register.";
            } elseif ($result === "exists") {
                $error = "That username or email is already registered.";
            } elseif ($result === "invalid_email") {
                $error = "Please enter a valid email address.";
            } elseif ($result === "weak_password") {
                $error = "Password must be at least 6 characters long.";
            } elseif (strpos($result, "db_error:") === 0) {
                $error = "A database error occurred. Please try again later.";
            } else {
                $error = "Something went wrong, please try again.";
            }

            // if we reach here, registration failed — show the user page with error message
            include __DIR__ . '/../Views/User.php';
            return;
        }
        include __DIR__ . '/../Views/User.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($username, $password);
            if ($user) {
                Session::set('user', $user);

                // ✅ Optional: redirect admins differently
                if (!empty($user['is_admin'])) {
                    header("Location: index.php?action=admin");
                } else {
                    header("Location: index.php?action=home");
                }
                exit;
            } else {
                echo "<p>Invalid username or password.</p>";
            }
        }
        include __DIR__ . '/../Views/User.php';
    }

    public function logout() {
        Session::destroy();
        header("Location: index.php?action=explore");
        exit;
    }

    // Block another user
    public function blockUser() {
        $currentUser = Session::get('user');
        if (!$currentUser) {
            header("Location: index.php?action=login");
            exit;
        }

        $blockedId = $_GET['user_id'] ?? null;
        if ($blockedId && $blockedId != $currentUser['user_id']) {
            $this->blockModel->blockUser($currentUser['user_id'], $blockedId);
        }

        header("Location: index.php?action=profile&user_id={$blockedId}");
        exit;
    }

    // Unblock a user
    public function unblockUser() {
        $currentUser = Session::get('user');
        if (!$currentUser) {
            header("Location: index.php?action=login");
            exit;
        }

        $blockedId = $_GET['user_id'] ?? null;
        if ($blockedId) {
            // Perform unblock action
            $success = $this->blockModel->unblockUser($currentUser['user_id'], $blockedId);

            // If unblock succeeded, remove from blocked list instantly (AJAX-friendly)
            if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
                header('Content-Type: application/json');
                echo json_encode(['success' => $success]);
                exit;
            }
        }

        // Default redirect for non-AJAX requests
        header("Location: index.php?action=settings&section=blocked");
        exit;
    }
}
?>