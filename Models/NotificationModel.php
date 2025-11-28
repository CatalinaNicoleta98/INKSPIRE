<?php

class NotificationModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
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
    /* Mark ONE notification as unread */
    public function markAsUnread($notification_id, $user_id)
    {
        $stmt = $this->db->prepare("
            UPDATE Notification
            SET is_read = 0
            WHERE notification_id = :nid AND user_id = :uid
        ");
        $stmt->bindParam(":nid", $notification_id);
        $stmt->bindParam(":uid", $user_id);
        return $stmt->execute();
    }

    /* Delete ONE notification */
    public function deleteNotification($notification_id, $user_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM Notification
            WHERE notification_id = :nid AND user_id = :uid
        ");
        $stmt->bindParam(":nid", $notification_id);
        $stmt->bindParam(":uid", $user_id);
        return $stmt->execute();
    }

    /* Delete ALL notifications for a user */
    public function deleteAllNotifications($user_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM Notification
            WHERE user_id = :uid
        ");
        $stmt->bindParam(":uid", $user_id);
        return $stmt->execute();
    }
}
?>
