<?php
class ApiController {
    private $service;

    public function __construct($service) {
        $this->service = $service;
    }

    public function processRequest() {
        // Anfragemethode bestimmen (GET, POST, PUT, DELETE)
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Routing basierend auf der Methode
        switch($method) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->sendResponse(405, json_encode(['message' => 'Methode nicht erlaubt']));
                break;
        }
    }

    private function handleGet() {
        $route = $_GET['route'] ?? 'home';
        
        switch($route) {
            case 'categories':
                $result = $this->service->getAllCategories();
                $this->sendResponse(200, json_encode($result));
                break;
            // Weitere Routen hier...
            default:
                $this->sendResponse(404, json_encode(['message' => 'Route nicht gefunden']));
                break;
        }
    }


    // Weitere Handler-Methoden
    // ...

    private function sendResponse($status, $body) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo $body;
    }
}
?>
