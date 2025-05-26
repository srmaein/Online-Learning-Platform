<?php
class Database {
    private $host = "localhost";
    private $db_name = "Online_Education";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // First try to connect to MySQL server
            $this->conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if database exists
            $stmt = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->db_name}'");
            if ($stmt->rowCount() == 0) {
                // Create database if it doesn't exist
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS {$this->db_name}");
            }

            // Connect to the specific database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Check if teachers table exists
            $stmt = $this->conn->query("SHOW TABLES LIKE 'teachers'");
            if ($stmt->rowCount() == 0) {
                // Create teachers table if it doesn't exist
                $this->conn->exec("CREATE TABLE IF NOT EXISTS teachers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    teacher_name VARCHAR(100) NOT NULL,
                    age INT NOT NULL,
                    date_of_birth DATE NOT NULL,
                    blood_group ENUM('A-', 'A+', 'B-', 'B+', 'AB-', 'AB+', 'O-', 'O+') NOT NULL,
                    phone_number VARCHAR(15) NOT NULL,
                    address TEXT NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    qualifications TEXT NOT NULL,
                    user_id VARCHAR(50) NOT NULL UNIQUE,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            }

            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                // Test query
                $stmt = $conn->query("SELECT COUNT(*) as count FROM teachers");
                $result = $stmt->fetch();
                return [
                    'status' => 'success',
                    'message' => 'Database connection successful',
                    'teacher_count' => $result['count']
                ];
            }
        } catch(Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
?> 