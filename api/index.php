<?php
//1-Seite
// Header für CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Einbinden der benötigten Dateien
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/DataModel.php';
require_once __DIR__ . '/services/DataService.php';
require_once __DIR__ . '/controllers/ApiController.php';

// Datenbankverbindung herstellen
$database = new Database();
$db = $database->getConnection();

// Instanzen erstellen
$model = new DataModel($db);
$service = new DataService($model);
$controller = new ApiController($service);

// URL-Routing für die API
$route = '';
if (isset($_GET['url'])) {
    $route = rtrim($_GET['url'], '/');
}
$_GET['route'] = $route;

// Anfrage verarbeiten
$controller->processRequest();
?>
