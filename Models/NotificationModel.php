<?php

class NotificationModel
{
    private $db;

    public function __construct($db = null)
    {
        if ($db !== null) {
            // Standard usage: controller passes in the PDO/DB connection
            $this->db = $db;
        } else {
            // Fallback usage: try to use a global DB connection already created (from config.php)
            // This allows calling new NotificationModel() inside views like Header.php
            if (isset($GLOBALS['db'])) {
                $this->db = $GLOBALS['db'];
            } elseif (isset($GLOBALS['pdo'])) {
                $this->db = $GLOBALS['pdo'];
            } else {
                // As an absolute fallback, try importing $db from global scope
                global $db, $pdo;
                if (isset($db)) {
                    $this->db = $db;
                } elseif (isset($pdo)) {
                    $this->db = $pdo;
                } else {
                    throw new Exception('Database connection not available for NotificationModel.');
                }
            }
        }
    }

    /* Get all notifications for a user, newest first */
    public function getNotificationsByUser($user_id)
    {
        $query = "
            SELECT 
                n.notification_id,
                n.type,
                n.post_id,
                n.comment_id,
                n.user_id AS receiver_id,
                n.actor_id,
                n.is_read,
                n.created_at,
                u.username AS actor_username,
                p.profile_picture AS actor_profile_picture
            FROM Notification n
            JOIN User u ON u.user_id = n.actor_id
            LEFT JOIN Profile p ON p.user_id = n.actor_id
            WHERE n.user_id = :user_id
            ORDER BY n.created_at DESC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Count unread notifications for badge */
    public function getUnreadCount($user_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Notification WHERE user_id = :user_id AND is_read = 0");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /* Mark ONE notification as read */
    public function markAsRead($notification_id, $user_id)
    {
        $stmt = $this->db->prepare("
            UPDATE Notification 
            SET is_read = 1 
            WHERE notification_id = :nid AND user_id = :uid
        ");
        $stmt->bindParam(":nid", $notification_id);
        $stmt->bindParam(":uid", $user_id);
        return $stmt->execute();
    }

    /* Mark ALL notifications as read */
    public function markAllAsRead($user_id)
    {
        $stmt = $this->db->prepare("
            UPDATE Notification 
            SET is_read = 1 
            WHERE user_id = :uid
        ");
        $stmt->bindParam(":uid", $user_id);
        return $stmt->execute();
    }

    /* Get a notification by ID */
    public function getNotification($notification_id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM Notification
            WHERE notification_id = :nid
        ");
        $stmt->bindParam(":nid", $notification_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
