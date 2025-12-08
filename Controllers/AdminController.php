<?php

class AdminController
{
    private $userModel;
    private $termsModel;
    private $aboutModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
        $this->termsModel = new TermsModel($db);
        $this->aboutModel = new AboutModel($db);
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

        $db = $this->userModel->getDb();
        $stmtUsersToday = $db->prepare("SELECT COUNT(*) FROM User WHERE DATE(created_at) = CURDATE()");
        $stmtUsersToday->execute();
        $newUsersToday = $stmtUsersToday->fetchColumn();

        $stmtPostsToday = $db->prepare("SELECT COUNT(*) FROM Post WHERE DATE(created_at) = CURDATE()");
        $stmtPostsToday->execute();
        $newPostsToday = $stmtPostsToday->fetchColumn();

        // Stats array for view
        $stats = [
            'new_users_today' => $newUsersToday,
            'new_posts_today' => $newPostsToday
        ];

        $terms = $this->termsModel->getTerms();
        $about = $this->aboutModel->getAbout();

        // Sidebar variables
        $db = $this->userModel->getDb();
        $sidebar = SidebarController::data($db);
        extract($sidebar);

        // Header variables
        $header = HeaderController::data($db, $loggedInUser);
        extract($header);

        // Render
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

    public function terms() {
        $this->ensureAdmin();
        $terms = $this->termsModel->getTerms();

        // Sidebar variables
        $db = $this->userModel->getDb();
        $sidebar = SidebarController::data($db);
        extract($sidebar);

        // Header variables
        $header = HeaderController::data($db, $loggedInUser);
        extract($header);

        include __DIR__ . '/../Views/AdminTerms.php';
    }

    public function updateTerms() {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
            $this->termsModel->updateTerms($_POST['content']);
        }
        header('Location: index.php?action=adminPanel');
        exit;
    }

    public function updateAbout() {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['about_content'])) {
            $this->aboutModel->updateAbout($_POST['about_content']);
        }
        header('Location: index.php?action=adminPanel');
        exit;
    }
}
?>