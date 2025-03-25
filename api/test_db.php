<?php
require_once __DIR__ . '/controllers/ApiController.php';

// Erstelle das Service-Objekt
$service = new ForumService();

// Instanziiere den ApiController mit dem Service-Objekt
$apiController = new ApiController($service);

// Teste die Methode isThreadAuthor
session_start();
$_SESSION['user_id'] = 123; // Simuliere einen Benutzer
$result = $apiController->isThreadAuthor(27);
var_dump($result);
