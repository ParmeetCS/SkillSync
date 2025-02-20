<?php
class Database {
    private $host = "localhost";
    private $db_name = "skillsync";
    private $username = "root";
    private $password = "";
    public $conn;
 
    public function getConnection() {
        $this->conn = null;
 
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
 
        return $this->conn;
    }
}
?>