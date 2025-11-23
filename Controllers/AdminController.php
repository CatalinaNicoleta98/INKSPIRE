

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

        // Get simple "today" stats (new users, new posts) via SQL views
        $stats = $this->userModel->getAdminDailyStats();

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
}
?>