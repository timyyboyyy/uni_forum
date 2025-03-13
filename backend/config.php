<?php
$servername = "db"; // Name des MySQL-Dienstes aus docker-compose.yml
$username = "user";
$password = "1234";
$dbname = "forum_db";

// Verbindung herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}
?>
