<?php
require_once __DIR__ . "/../Models/UserModel.php";
require_once __DIR__ . "/../helpers/Session.php";

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $dob = $_POST['dob'] ?? ''; // date of birth from the form

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
        header("Location: index.php?action=login");
        exit;
    }
}
?>