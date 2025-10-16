<?php
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $db_name = "InkspireDB";
        private $username = "catalina"; // change if needed
        private $password = "356911";   // change if needed
        public $conn;

        public function connect() {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name};charset=utf8", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Connection error: " . $e->getMessage());
            }
            return $this->conn;
        }
    }
}

// Create a single PDO instance for global use
$database = new Database();
$pdo = $database->connect();
?>