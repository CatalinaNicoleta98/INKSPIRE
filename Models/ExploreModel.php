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
                    p.title, 
                    p.description, 
                    p.image_url,
                    p.created_at,
                    pr.display_name AS author,
                    COUNT(DISTINCT l.like_id) AS likes,
                    COUNT(DISTINCT c.comment_id) AS comments
                FROM Post p
                LEFT JOIN Profile pr ON pr.profile_id = p.user_id
                LEFT JOIN `Like` l ON l.post_id = p.post_id
                LEFT JOIN Comment c ON c.post_id = p.post_id
                GROUP BY p.post_id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}