<?php

class NotificationController
{
    private $notificationModel;
    private $postModel;
    private $profileModel;

    public function __construct($db)
    {
        $this->notificationModel = new NotificationModel($db);
        $this->postModel = new PostModel($db);
        $this->profileModel = new ProfileModel($db);
    }

    /* Show notifications list */
    public function index()
    {
        if (!isset($_SESSION['user']['user_id'])) {
            header("Location: index.php?action=login");
            return;
        }

        $user_id = $_SESSION['user']['user_id'];
        $notifications = $this->notificationModel->getNotificationsByUser($user_id);

        // Mark all notifications as read when viewing the page
        $this->notificationModel->markAllAsRead($user_id);

        include 'Views/Notifications.php';
    }

    /* Process click on notification */
    public function view()
    {
        if (!isset($_SESSION['user']['user_id'])) {
            header("Location: index.php?action=login");
            return;
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=notifications");
            return;
        }

        $notification_id = intval($_GET['id']);
        $user_id = $_SESSION['user']['user_id'];

        $notif = $this->notificationModel->getNotification($notification_id);

        if (!$notif || $notif['user_id'] != $user_id) {
            header("Location: index.php?action=notifications");
            return;
        }

        // Mark it as read
        $this->notificationModel->markAsRead($notification_id, $user_id);

        /* Redirect based on type */
        switch ($notif['type']) {

            case 'like':
                header("Location: index.php?action=post&id=" . $notif['post_id']);
                break;

            case 'comment':
            case 'reply':
                header("Location: index.php?action=post&id=" . $notif['post_id'] . "&comment_id=" . $notif['comment_id']);
                break;

            case 'follow':
                // actor_id = follower → their profile should open
                $profile = $this->profileModel->getProfileByUserId($notif['actor_id']);
                $profileId = $profile['profile_id'] ?? null;

                if ($profileId) {
                    header("Location: index.php?action=profile&user_id=" . $profileId);
                } else {
                    header("Location: index.php?action=notifications");
                }
                break;

            default:
                header("Location: index.php?action=notifications");
                break;
        }
    }
}
