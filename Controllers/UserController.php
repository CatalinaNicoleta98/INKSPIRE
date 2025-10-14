<?php
require_once __DIR__ . "/../models/UserModel.php";
require_once __DIR__ . "/../helpers/Session.php";

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        Session::start();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $dob = $_POST['dob'] ?? '';

            $result = $this->userModel->register($firstName, $lastName, $email, $username, $password, $dob);

            if ($result === true) {
                header("Location: index.php?action=login");
                exit;
            } elseif ($result === "too_young") {
                $error = "You must be at least 14 years old to register.";
            } elseif ($result === "exists") {
                $error = "Username or email already exists.";
            } else {
                $error = "Error: could not register user.";
            }
        }
        include __DIR__ . '/../views/User.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($username, $password);

            if (is_array($user)) {
                Session::set('user', $user);
                if ($user['is_admin']) {
                    header("Location: index.php?action=admin");
                } else {
                    header("Location: index.php?action=home");
                }
                exit;
            } elseif ($user === "blocked") {
                $error = "Your account has been blocked by an admin.";
            } else {
                $error = "Invalid username or password.";
            }
        }
        include __DIR__ . '/../views/User.php';
    }

    public function logout() {
        Session::destroy();
        header("Location: index.php?action=login");
        exit;
    }
}
?>