<?php
require __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "Datenbankverbindung erfolgreich!";
        // Testabfrage
        $stmt = $conn->query("SELECT COUNT(*) AS count FROM categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<br>Anzahl Kategorien: " . $result['count'];
    }
} catch(PDOException $e) {
    echo "Verbindungsfehler: " . $e->getMessage();
}
