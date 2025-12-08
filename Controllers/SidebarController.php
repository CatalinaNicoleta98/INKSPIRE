<?php

class SidebarController
{
    public static function data($db)
    {
        // Load About text (was previously done inside the Sidebar view)
        $aboutModel = new AboutModel($db);
        $aboutText = $aboutModel->getAbout();

        // Session-based presentation logic
        $isLoggedIn   = Session::isLoggedIn();
        $loggedInUser = $_SESSION['user'] ?? null;
        $isAdmin      = $loggedInUser && !empty($loggedInUser['is_admin']);
        $adminViewOn  = !empty($_SESSION['admin_view']);

        // CSRF token for post creation
        if (!isset($_SESSION['post_token'])) {
            $_SESSION['post_token'] = bin2hex(random_bytes(32));
        }
        $postToken = $_SESSION['post_token'];

        // Return all sidebar variables to controllers
        return [
            'aboutText'    => $aboutText,
            'isLoggedIn'   => $isLoggedIn,
            'loggedInUser' => $loggedInUser,
            'isAdmin'      => $isAdmin,
            'adminViewOn'  => $adminViewOn,
            'postToken'    => $postToken
        ];
    }
}
