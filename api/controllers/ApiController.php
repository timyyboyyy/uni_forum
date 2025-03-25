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
                        'userId' => $_SESSION['user_id'],
                        'isAdmin' => (isset($_SESSION['rules_id']) && $_SESSION['rules_id'] == 1)
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
                
            case 'admin_check':
                session_start();
                $isAdmin = $this->isAdmin();
                $this->sendResponse(200, json_encode(['isAdmin' => $isAdmin]));
                break;
                
            case 'admin/dashboard':
                // Admin-Rechte prüfen
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['message' => 'Nur für Administratoren']));
                    break;
                }
                
                $result = $this->service->getAdminDashboard();
                $this->sendResponse(200, json_encode($result));
                break;
            
            case 'admin/users':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['message' => 'Nur für Administratoren']));
                    break;
                }
            
                $parts = explode('/', $_GET['route']);
                if (count($parts) > 2 && is_numeric($parts[2])) {
                    $user_id = (int)$parts[2];
                    $result = $this->service->getUserById($user_id);
                    $this->sendResponse(200, json_encode($result));
                } else {
                    $result = $this->service->getAllUsers();
                    $this->sendResponse(200, json_encode($result));
                }
                break;

            case (preg_match('/^admin\/users\/(\d+)$/', $route, $matches) ? true : false):
                $user_id = (int)$matches[1];
                $result = $this->service->getUserById($user_id);
                $this->sendResponse(200, json_encode($result));
                break;
            
            case 'admin/categories':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['message' => 'Nur für Administratoren']));
                    break;
                }
                
                $result = $this->service->getAllCategories(); // Diese Methode existiert bereits
                $this->sendResponse(200, json_encode($result));
                break;
            
            case (preg_match('/^admin\/category\/(\d+)$/', $route, $matches) ? true : false):
                $category_id = (int)$matches[1];
                $result = $this->service->getCategoryById($category_id);
                $this->sendResponse(200, json_encode($result));
                break;

            case 'admin/threads':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['message' => 'Nur für Administratoren']));
                    break;
                }
                
                $result = $this->service->getAllThreads();
                $this->sendResponse(200, json_encode($result));
                break;
            
            case 'admin/posts':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['message' => 'Nur für Administratoren']));
                    break;
                }
                
                $result = $this->service->getAllPosts();
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
            
            case 'admin_delete_reply':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['success' => false, 'message' => 'Nur für Administratoren']));
                    break;
                }
                
                if (!isset($input['reply_id'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Antwort-ID erforderlich']));
                    break;
                }
                
                // Hier Methode zum Löschen der Antwort implementieren
                // $result = $this->service->deleteReply($input['reply_id']);
                
                $this->sendResponse(200, json_encode(['success' => true, 'message' => 'Antwort gelöscht']));
                break;
                
            case 'admin/user/':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['success' => false, 'message' => 'Nur für Administratoren']));
                    break;
                }

            case (preg_match('/^admin\/user\/(\d+)$/', $route, $matches) ? true : false):
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['success' => false, 'message' => 'Nur für Administratoren']));
                    break;
                }
                
                $user_id = (int)$matches[1];
                $input = json_decode(file_get_contents('php://input'), true);
                if (!isset($input['username']) || !isset($input['email']) || !isset($input['role_id'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->updateUser($user_id, $input['username'], $input['email'], $input['role_id']);
                $this->sendResponse(200, json_encode($result));
                break;
                
                
                // Benutzerbearbeitung
                $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($requestMethod === 'PUT') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$user_id || !isset($input['username']) || !isset($input['email']) || !isset($input['role_id'])) {
                        $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                        break;
                    }
                    
                    $result = $this->service->updateUser($user_id, $input['username'], $input['email'], $input['role_id']);
                    $this->sendResponse(200, json_encode($result));
                } else {
                    $this->sendResponse(405, json_encode(['success' => false, 'message' => 'Methode nicht erlaubt']));
                }
                break;

            case (preg_match('/^admin\/category\/(\d+)$/', $route, $matches) ? true : false):
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode(['success' => false, 'message' => 'Nur für Administratoren']));
                    break;
                }
                
                $category_id = (int)$matches[1];
                $input = json_decode(file_get_contents('php://input'), true);
                if (!isset($input['name']) || !isset($input['description'])) {
                    $this->sendResponse(400, json_encode(['success' => false, 'message' => 'Unvollständige Daten']));
                    break;
                }
                
                $result = $this->service->updateCategory($category_id, $input['name'], $input['description']);
                $this->sendResponse(200, json_encode($result));
                break;
            
            // Kategoriebearbeitung
            
                
            case 'admin/categories':
                if (!$this->isAdmin()) {
                    $this->sendResponse(403, json_encode([
                        'success' => false,
                        'message' => 'Nur für Administratoren'
                    ]));
                    break;
                }
    
                if (!isset($input['name']) || !isset($input['description'])) {
                    $this->sendResponse(400, json_encode([
                        'success' => false,
                        'message' => 'Name und Beschreibung erforderlich'
                    ]));
                    break;
                }
    
                try {
                    $result = $this->service->createCategory(
                        htmlspecialchars($input['name']),
                        htmlspecialchars($input['description'])
                    );
                    
                    $this->sendResponse(201, json_encode([
                        'success' => true,
                        'data' => $result
                    ]));
                    
                } catch (RuntimeException $e) {
                    $this->sendResponse(409, json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]));
                }
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

    private function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['rules_id']) && $_SESSION['rules_id'] == 1; // 1 = Admin laut init.sql
    }
    
    private function isThreadOwner($thread_id) {
        // Stellen Sie sicher, dass die Session gestartet ist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        // Überprüfen Sie, ob ein Benutzer eingeloggt ist
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
    
        // Holen Sie den Thread aus der Datenbank
        $thread = $this->service->getThreadById($thread_id);
    
        // Überprüfen Sie, ob der Thread existiert und ob der eingeloggte Benutzer der Besitzer ist
        if ($thread && isset($thread['user_id'])) {
            return $thread['user_id'] == $_SESSION['user_id'];
        }
    
        // Wenn etwas schief geht, geben Sie false zurück
        return false;
    }

    // Füge diese Funktion für Post-Besitzerüberprüfung hinzu
    private function isPostOwner($post_id) {
        // Stellen Sie sicher, dass die Session gestartet ist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        // Überprüfen Sie, ob ein Benutzer eingeloggt ist
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
    
        // Holen Sie den Post aus der Datenbank
        $post = $this->service->getPostById($post_id);
    
        // Überprüfen Sie, ob der Post existiert und ob der eingeloggte Benutzer der Besitzer ist
        if ($post && isset($post['user_id'])) {
            // Typ-Konvertierung sicherstellen
            return (int)$post['user_id'] === (int)$_SESSION['user_id'];
        }
    
        // Wenn etwas schief geht, geben Sie false zurück
        return false;
    }

    private function isAccountOwner($user_id) {
        // Prüfung, ob die Session-ID mit der Ziel-ID übereinstimmt
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$user_id;
    }
    
    

    public function handleDelete() {
        try {
            $urlParts = explode('/', $_SERVER['REQUEST_URI']);
            
            // Normale User-Löschvorgänge (Threads/Posts)
            if (strpos($_SERVER['REQUEST_URI'], '/api/thread/') === 0) {
                $thread_id = $urlParts[3] ?? null;
                if (!$thread_id || !is_numeric($thread_id)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Ungültige Thread-ID']);
                    return;
                }
    
                if ($this->isAdmin() || $this->isThreadOwner($thread_id)) {
                    $success = $this->service->deleteThread($thread_id);
                    echo json_encode(['success' => $success]);
                    return;
                }
    
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Zugriff verweigert']);
                return;
            }
    
            if (strpos($_SERVER['REQUEST_URI'], '/api/delete-post/') === 0) {
                $post_id = $urlParts[3] ?? null;
                if (!$post_id || !is_numeric($post_id)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Ungültige Post-ID']);
                    return;
                }
    
                if ($this->isAdmin() || $this->isPostOwner($post_id)) {
                    $success = $this->service->deletePost($post_id);
                    echo json_encode(['success' => $success]);
                    return;
                }
    
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Zugriff verweigert']);
                return;
            }
    
            // Admin-Löschvorgänge
            if (strpos($_SERVER['REQUEST_URI'], '/api/admin/') === 0) {
                if (!$this->isAdmin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Nur für Admins']);
                    return;
                }
    
                $entityType = $urlParts[3] ?? null;
                $entityId = $urlParts[4] ?? null;
    
                if (!$entityType || !$entityId || !is_numeric($entityId)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Ungültige Parameter']);
                    return;
                }
    
                $success = false;
                switch ($entityType) {
                    case 'user':
                        $success = $this->service->deleteUser($entityId);
                        break;
                    case 'category':
                        $success = $this->service->deleteCategory($entityId);
                        break;
                    case 'thread':
                        $success = $this->service->deleteThread($entityId);
                        break;
                    case 'post':
                        $success = $this->service->deletePost($entityId);
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Ungültiger Entitätstyp']);
                        return;
                }
    
                echo json_encode(['success' => $success]);
                return;
            }

            // User-Konto Löschung
            if (strpos($_SERVER['REQUEST_URI'], '/api/delete-account') === 0) {
                session_start();
                
                if (!isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Nicht authentifiziert']);
                    return;
                }
            
                $current_user_id = $_SESSION['user_id'];
                
                // Berechtigungsprüfung
                if (!$this->isAdmin() && !$this->isAccountOwner($current_user_id)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Zugriff verweigert']);
                    return;
                }
            
                $success = $this->service->deleteUser($current_user_id);
                
                if ($success) {
                    session_destroy();
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Löschen fehlgeschlagen']);
                }
                return;
            }
    
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint nicht gefunden']);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Serverfehler: ' . $e->getMessage()
            ]);
        }
    }
    
    
}
?>
