<?php
//2-Seite
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
            // In der handleGet-Methode innerhalb des switch-Blocks
            case 'latest_posts':
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $result = $this->service->getLatestPosts($limit);
                $this->sendResponse(200, json_encode($result));
                break;

            case 'thread':
                if (!isset($_GET['id'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Thread-ID erforderlich']));
                    break;
                }
                
                $thread_id = (int)$_GET['id'];
                $result = $this->service->getThread($thread_id);
                
                if ($result) {
                    $this->sendResponse(200, json_encode($result));
                } else {
                    $this->sendResponse(404, json_encode(['message' => 'Thread nicht gefunden']));
                }
                break;
            // In der handleGet-Methode innerhalb des switch-Blocks hinzufügen
            case 'category_threads':
                if (!isset($_GET['id'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Kategorie-ID erforderlich']));
                    break;
                }
                
                $category_id = (int)$_GET['id'];
                $result = $this->service->getThreadsByCategory($category_id);
                $this->sendResponse(200, json_encode($result));
                break;

            // Weitere Routen hier...
            default:
                $this->sendResponse(404, json_encode(['message' => 'Route nicht gefunden']));
                break;
        }
    }
    
    private function handlePost() {
        $route = $_GET['route'] ?? '';
        
        // JSON-Daten aus dem Request-Body lesen
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE);
        
        switch($route) {
            case 'posts':
                // Überprüfen der erforderlichen Daten
                if (!isset($input['title']) || !isset($input['category_id']) || !isset($input['content']) || !isset($input['user_id'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Unvollständige Daten']));
                    break;
                }
                
                // Thread mit Content erstellen
                $result = $this->service->createThread(
                    $input['title'],
                    $input['content'],
                    $input['category_id'],
                    $input['user_id']
                );
                
                if ($result) {
                    $this->sendResponse(201, json_encode(['message' => 'Thread erfolgreich erstellt', 'thread_id' => $result]));
                } else {
                    $this->sendResponse(500, json_encode(['message' => 'Fehler beim Erstellen des Threads']));
                }
                break;

            case 'reply':
                if (!isset($input['thread_id']) || !isset($input['content']) || !isset($input['user_id'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->createThreadReply(
                    $input['thread_id'],
                    $input['user_id'],
                    $input['content']
                );
                
                if ($result) {
                    $this->sendResponse(201, json_encode(['message' => 'Antwort erfolgreich erstellt', 'reply_id' => $result]));
                } else {
                    $this->sendResponse(500, json_encode(['message' => 'Fehler beim Erstellen der Antwort']));
                }
                break;
                
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
