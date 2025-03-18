<?php
//2-Seite
class ApiController {
    private $service;

    public function __construct($service) {
        $this->service = $service;
    }

    public function processRequest() {
        // Anfragemethode bestimmen (GET, POST, PUT, DELETE)S
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
            
            case 'category_threads':
                if (!isset($_GET['id'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Kategorie-ID erforderlich']));
                    break;
                }
                
                $category_id = (int)$_GET['id'];
                $result = $this->service->getThreadsByCategory($category_id);
                $this->sendResponse(200, json_encode($result));
                break;

            case 'check-auth':
                session_start();
                if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
                    $this->sendResponse(200, json_encode([
                        'loggedIn' => true,
                        'username' => $_SESSION['username'],
                        'userId' => $_SESSION['user_id']
                    ]));
                } else {
                    $this->sendResponse(200, json_encode([
                        'loggedIn' => false
                    ]));
                }
                break;
            
            case 'top_categories':
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
                $result = $this->service->getTopCategories($limit);
                $this->sendResponse(200, json_encode($result));
                break;
                
            case 'user-profile':
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['message' => 'Nicht autorisiert']));
                    break;
                }
                
                $result = $this->service->getUserProfile($_SESSION['user_id']);
                $this->sendResponse(200, json_encode($result));
                break;
            
            case 'user-activity':
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['message' => 'Nicht autorisiert']));
                    break;
                }
                
                $result = $this->service->getUserActivity($_SESSION['user_id']);
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
                // Session starten
                session_start();
                
                // Prüfen, ob der Benutzer eingeloggt ist
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['message' => 'Nicht autorisiert. Bitte zuerst einloggen.']));
                    break;
                }
                
                // Überprüfen der erforderlichen Daten
                if (!isset($input['title']) || !isset($input['category_id']) || !isset($input['content'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Unvollständige Daten']));
                    break;
                }
                
                // User_id aus der Session verwenden, nicht aus dem Request
                $user_id = $_SESSION['user_id'];
                
                // Thread mit Content erstellen
                $result = $this->service->createThread(
                    $input['title'],
                    $input['content'],
                    $input['category_id'],
                    $user_id  // Gesicherte user_id aus der Session
                );
                
                if ($result) {
                    $this->sendResponse(201, json_encode(['message' => 'Thread erfolgreich erstellt', 'thread_id' => $result]));
                } else {
                    $this->sendResponse(500, json_encode(['message' => 'Fehler beim Erstellen des Threads']));
                }
                break;
            

            case 'reply':
                // Session starten
                session_start();
                
                // Prüfen, ob der Benutzer eingeloggt ist
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['message' => 'Nicht autorisiert. Bitte zuerst einloggen.']));
                    break;
                }
                
                if (!isset($input['thread_id']) || !isset($input['content'])) {
                    $this->sendResponse(400, json_encode(['message' => 'Unvollständige Daten']));
                    break;
                }
                
                // User_id aus der Session verwenden
                $user_id = $_SESSION['user_id'];
                
                $result = $this->service->createThreadReply(
                    $input['thread_id'],
                    $user_id,  // Gesicherte user_id aus der Session
                    $input['content']
                );
                
                if ($result) {
                    $this->sendResponse(201, json_encode(['message' => 'Antwort erfolgreich erstellt', 'reply_id' => $result]));
                } else {
                    $this->sendResponse(500, json_encode(['message' => 'Fehler beim Erstellen der Antwort']));
                }
                break;
                

            case 'register':
                if (!isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->registerUser(
                    $input['username'],
                    $input['email'],
                    $input['password']
                );
                
                if ($result['success']) {
                    // Session starten und Benutzer einloggen
                    session_start();
                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['username'] = $input['username'];
                    
                    $this->sendResponse(201, json_encode(['success' => true, 'message' => 'Registrierung erfolgreich']));
                } else {
                    $this->sendResponse(400, json_encode($result));
                }
                break;
                
            case 'login':
                if (!isset($input['username']) || !isset($input['password'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->loginUser(
                    $input['username'],
                    $input['password']
                );
                
                if ($result['success']) {
                    // Session starten und Benutzer einloggen
                    session_start();
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['username'] = $result['user']['username'];
                    $_SESSION['rules_id'] = $result['user']['rules_id'];
                    
                    $this->sendResponse(200, json_encode(['success' => true, 'message' => 'Login erfolgreich']));
                } else {
                    $this->sendResponse(401, json_encode($result));
                }
                break;
                
            case 'logout':
                session_start();
                session_destroy();
                $this->sendResponse(200, json_encode(['success' => true, 'message' => 'Logout erfolgreich']));
                break;

            case 'update-profile':
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['success' => false, 'message' => 'Nicht autorisiert']));
                    break;
                }
                
                if (!isset($input['username']) || !isset($input['email'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->updateUserProfile(
                    $_SESSION['user_id'],
                    $input['username'],
                    $input['email']
                );
                
                if ($result['success']) {
                    // Aktualisiere den Benutzernamen in der Session
                    $_SESSION['username'] = $input['username'];
                    $this->sendResponse(200, json_encode(['success' => true, 'message' => 'Profil aktualisiert']));
                } else {
                    $this->sendResponse(400, json_encode($result));
                }
                break;
            
            case 'update-password':
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    $this->sendResponse(401, json_encode(['success' => false, 'message' => 'Nicht autorisiert']));
                    break;
                }
                
                if (!isset($input['current_password']) || !isset($input['new_password'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->updateUserPassword(
                    $_SESSION['user_id'],
                    $input['current_password'],
                    $input['new_password']
                );
                
                if ($result['success']) {
                    $this->sendResponse(200, json_encode(['success' => true, 'message' => 'Passwort aktualisiert']));
                } else {
                    $this->sendResponse(400, json_encode($result));
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
