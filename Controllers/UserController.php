<?php
require_once __DIR__ . "/../models/UserModel.php";
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

            if ($this->userModel->register($firstName, $lastName, $email, $username, $password)) {
                header("Location: index.php?action=login");
                exit;
            } else {
                echo "Error: could not register user.";
            }
        }
        include __DIR__ . '/../views/User.php';
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
        include __DIR__ . '/../views/User.php';
    }

    public function logout() {
        Session::destroy();
        header("Location: index.php?action=login");
        exit;
    }
}
?>