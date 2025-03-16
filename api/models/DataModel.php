<?php
//4-Seite
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
            c.name AS 'kategorie',
            COUNT(DISTINCT t.ID) AS 'themen_anzahl',
            IFNULL(
                (
                    SELECT
                        CONCAT(
                            DATE_FORMAT(contrib.created_at, '%d.%m.%Y, %H:%i'),
                            ' Uhr von ',
                            u.username
                        )
                    FROM
                        contributions contrib
                    JOIN
                        threads t2 ON contrib.threads_ID = t2.ID
                    JOIN
                        users u ON contrib.users_ID = u.ID
                    WHERE
                        t2.categories_ID = c.ID
                    ORDER BY
                        contrib.created_at DESC
                    LIMIT 1
                ),
                'Keine Beiträge'
            ) AS 'letzter_beitrag'
        FROM
            categories c
        LEFT JOIN
            threads t ON c.ID = t.categories_ID
        GROUP BY
            c.ID
        ORDER BY
            c.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function createThread($title, $content, $category_id, $user_id) {
        $query = "INSERT INTO threads (titel, content, created_at, categories_ID, users_ID) 
                  VALUES (:title, :content, NOW(), :category_id, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getLatestPosts($limit = 10) {
        $query = "SELECT 
                    t.ID as thread_id,
                    t.titel as title,
                    c.name as category,
                    u.username as author,
                    t.created_at as date
                  FROM 
                    threads t
                  JOIN 
                    categories c ON t.categories_ID = c.ID
                  JOIN 
                    users u ON t.users_ID = u.ID
                  ORDER BY 
                    t.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    

}
?>
