<?php
class Database {
    private $host;  // Docker-Service-Name
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = getenv('MYSQL_HOST') ?: 'mysql-db';
        $this->db_name = getenv('MYSQL_DATABASE') ?: 'meine_datenbank';
        $this->username = getenv('MYSQL_USER') ?: 'benutzer';
        $this->password = getenv('MYSQL_PASSWORD') ?: 'passwort';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Verbindungsfehler: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
