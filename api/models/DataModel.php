<?php
class DataModel {
    private $conn;
    private $table_name = "ihre_tabelle";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Daten abrufen
    public function getData() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCategories() {
        $query = "SELECT 
                    c.id, 
                    c.name
                FROM 
                    categories c";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

}
?>
