<?php

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AdminController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Ensure the current user is an admin.
     * Redirects to home if not authorized.
     */
    private function ensureAdmin()
    {
        Session::start();
        $user = Session::get('user');

        if (!$user || empty($user['is_admin'])) {
            header('Location: index.php?action=home');
            exit;
        }

        return $user;
    }

    /**
     * Admin dashboard: user overview + simple stats.
     */
    public function dashboard()
    {
        $this->ensureAdmin();

        // Get all users with aggregated stats for the table
        $users = $this->userModel->getAdminUserOverview();

        // Daily statistics (new users today, new posts today)
        require_once __DIR__ . '/../config.php';
        $database = new Database();
        $db = $database->connect();

        // New users today
        $stmtUsersToday = $db->prepare("SELECT COUNT(*) FROM User WHERE DATE(created_at) = CURDATE()");
        $stmtUsersToday->execute();
        $newUsersToday = $stmtUsersToday->fetchColumn();

        // New posts today
        $stmtPostsToday = $db->prepare("SELECT COUNT(*) FROM Post WHERE DATE(created_at) = CURDATE()");
        $stmtPostsToday->execute();
        $newPostsToday = $stmtPostsToday->fetchColumn();

        // Stats array for view
        $stats = [
            'new_users_today' => $newUsersToday,
            'new_posts_today' => $newPostsToday
        ];

        // Reuse existing AdminPanel.php view as admin panel container
        // (this file can be adapted to render $users and $stats)
        include __DIR__ . '/../Views/AdminPanel.php';
    }

    /**
     * Toggle global block / unblock for a user.
     * Expects POST: user_id, block (1 or 0).
     */
    public function toggleBlock()
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $block  = isset($_POST['block']) ? (int)$_POST['block'] : 0;

            if ($userId > 0) {
                $this->userModel->setGlobalBlock($userId, $block === 1);
            }
        }

        header('Location: index.php?action=adminPanel');
        exit;
    }

    /**
     * Promote / demote a user to/from admin.
     * Expects POST: user_id, is_admin (1 or 0).
     */
    public function toggleAdmin()
    {
        $current = $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId  = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $isAdmin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;

            // Prevent an admin from removing their own admin rights
            if ($userId > 0 && $userId !== (int)$current['user_id']) {
                $this->userModel->setAdminFlag($userId, $isAdmin === 1);
            }
        }

        header('Location: index.php?action=adminPanel');
        exit;
    }
    /**
     * Globally block a user (admin-only).
     */
    public function globalBlock()
    {
        $this->ensureAdmin();

        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($userId > 0) {
            $this->userModel->setGlobalBlockStatus($userId, 1);
        }

        header("Location: index.php?action=adminPanel");
        exit;
    }

    /**
     * Remove global block from a user (admin-only).
     */
    public function globalUnblock()
    {
        $this->ensureAdmin();

        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($userId > 0) {
            $this->userModel->setGlobalBlockStatus($userId, 0);
        }

        header("Location: index.php?action=adminPanel");
        exit;
    }
}
?>