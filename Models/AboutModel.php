<?php
class AboutModel {
    private $db;

    public function __construct($db) {
        if (!$db) {
            throw new Exception("Database connection not available for AboutModel.");
        }
        $this->db = $db;
    }

    public function getAbout() {
        $stmt = $this->db->prepare("SELECT content FROM About LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function updateAbout($content) {
        $stmt = $this->db->prepare("UPDATE About SET content = :content WHERE id = 1");
        return $stmt->execute(['content' => $content]);
    }
}