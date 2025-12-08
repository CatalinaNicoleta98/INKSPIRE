<?php

class HeaderController
{
    /**
     * Prepares all header-related variables previously inside Header.php.
     * Controllers must pass $db (database connection) and $loggedInUser (from Session).
     */
    public static function data($db, $loggedInUser)
    {
        // Default fallback values
        $profilePic = 'uploads/default_avatar.png';
        $unread = 0;
        $notifications = [];
        $unreadNotifications = [];
        $readNotifications = [];

        if ($loggedInUser) {
            // Load profile picture
            $profileModel = new ProfileModel($db);
            $profile = $profileModel->getProfileByUserId($loggedInUser['user_id']);

            if ($profile && !empty($profile['profile_picture'])) {
                $profilePic = htmlspecialchars($profile['profile_picture']);
            }

            // Load notifications
            $notifModel = new NotificationModel($db);
            $notifications = $notifModel->getNotificationsByUser($loggedInUser['user_id']);

            // Count unread notifications
            $unread = 0;
            foreach ($notifications as $n) {
                if (empty($n['is_read'])) {
                    $unreadNotifications[] = $n;
                    $unread++;
                } else {
                    $readNotifications[] = $n;
                }
            }
        }

        return [
            'loggedInUser'        => $loggedInUser,
            'profilePic'          => $profilePic,
            'unread'              => $unread,
            'notifications'       => $notifications,
            'unreadNotifications' => $unreadNotifications,
            'readNotifications'   => $readNotifications
        ];
    }
}

?>
