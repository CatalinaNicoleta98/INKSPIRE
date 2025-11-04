<?php
require_once __DIR__ . '/../config.php';

class ExploreModel {
    private $db;

    public function __construct() {
        require __DIR__ . '/../config.php';
        $this->db = $pdo; // or $conn if your config defines that variable
    }

    public function getExplorePosts() {
        $sql = "SELECT 
                    p.post_id,
                    p.user_id,
                    p.title, 
                    p.description, 
                    p.image_url,
                    p.created_at,
                    u.username,
                    pr.profile_picture,
                    p.is_public,
                    COUNT(DISTINCT l.like_id) AS likes,
                    COUNT(DISTINCT c.comment_id) AS comments
                FROM Post p
                JOIN User u ON p.user_id = u.user_id
                LEFT JOIN Profile pr ON u.user_id = pr.user_id
                LEFT JOIN `Like` l ON l.post_id = p.post_id
                LEFT JOIN Comment c ON c.post_id = p.post_id
                WHERE p.is_public = 1
                GROUP BY p.post_id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>