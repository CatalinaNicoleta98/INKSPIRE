<?php

class TermsModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTerms() {
        $stmt = $this->db->prepare("SELECT content FROM terms WHERE id = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function updateTerms($content) {
        $stmt = $this->db->prepare("UPDATE terms SET content = :content WHERE id = 1");
        return $stmt->execute([':content' => $content]);
    }
}
