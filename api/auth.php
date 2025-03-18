<?php
function checkAuth() {
    // Session starten, wenn noch nicht geschehen
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Prüfen, ob Benutzer eingeloggt ist
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'rules_id' => $_SESSION['rules_id'] ?? 0
    ];
}
?>
