<?php
class DataService {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function getAllData() {
        $stmt = $this->model->getData();
        $data = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        
        return $data;
    }

    public function getAllCategories() {
        $stmt = $this->model->getCategories();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['kategorie'],
                'description' => $row['beschreibung'],
                'created_at' => $row['erstellt_am'],
                'topic_count' => $row['themen_anzahl'],
                'last_post' => $row['letzter_beitrag']
            ];
        }
        
        return $categories;
    }
    
    
    public function createThread($title, $content, $category_id, $user_id) {
        return $this->model->createThread($title, $content, $category_id, $user_id);
    }
    
    public function getLatestPosts($limit = 10) {
        $stmt = $this->model->getLatestPosts($limit);
        $posts = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = [
                'id' => $row['thread_id'],
                'title' => $row['title'],
                'category' => $row['category'],
                'author' => $row['author'],
                'date' => date('d.m.Y, H:i', strtotime($row['date'])) . ' Uhr'
            ];
        }
        
        return $posts;
    }

    public function getThread($thread_id) {
        $thread = $this->model->getThreadById($thread_id);
        
        if (!$thread) {
            return null;
        }
        
        // Antworten abrufen
        $stmt = $this->model->getThreadReplies($thread_id);
        $replies = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replies[] = [
                'id' => $row['ID'],
                'content' => $row['content'],
                'author' => $row['author'],
                'date' => date('d.m.Y, H:i', strtotime($row['created_at'])) . ' Uhr'
            ];
        }
        
        return [
            'thread' => [
                'id' => $thread['ID'],
                'title' => $thread['titel'],
                'content' => $thread['content'],
                'author' => $thread['author'],
                'category' => $thread['category_name'],
                'date' => date('d.m.Y, H:i', strtotime($thread['created_at'])) . ' Uhr'
            ],
            'replies' => $replies
        ];
    }
    
    public function createThreadReply($thread_id, $user_id, $content) {
        return $this->model->createReply($thread_id, $user_id, $content);
    }
    
    public function getThreadsByCategory($category_id) {
        $stmt = $this->model->getThreadsByCategory($category_id);
        $threads = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $threads[] = [
                'id' => $row['ID'],
                'title' => $row['titel'],
                'author' => $row['author'],
                'date' => date('d.m.Y, H:i', strtotime($row['created_at'])) . ' Uhr',
                'reply_count' => $row['reply_count']
            ];
        }
        
        return $threads;
    }

    public function registerUser($username, $email, $password) {
        return $this->model->registerUser($username, $email, $password);
    }
    
    public function loginUser($username, $password) {
        return $this->model->loginUser($username, $password);
    }
    
    public function getTopCategories($limit = 5) {
        $stmt = $this->model->getCategories($limit);
        $categories = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['kategorie'],
                'topic_count' => $row['themen_anzahl'],
                'last_post' => $row['letzter_beitrag']
            ];
        }
        
        return $categories;
    }

    public function getUserProfile($user_id) {
        return $this->model->getUserProfile($user_id);
    }
    
    public function getUserActivity($user_id) {
        return $this->model->getUserActivity($user_id);
    }
    
    public function updateUserProfile($user_id, $username, $email) {
        return $this->model->updateUserProfile($user_id, $username, $email);
    }
    
    public function updateUserPassword($user_id, $current_password, $new_password) {
        return $this->model->updateUserPassword($user_id, $current_password, $new_password);
    }
    
    public function getAllUsers() {
        $stmt = $this->model->getAllUsers();
        $users = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'id' => $row['ID'],
                'username' => $row['username'],
                'email' => $row['email'],
                'role' => $row['rules_ID'] == 1 ? 'Admin' : 'User',
                'role_id' => $row['rules_ID'],
                'created_at' => $row['created_at']
            ];
        }
        
        return $users;
    }
    
    public function getAdminDashboard() {
        // Benutzeranzahl
        $userCount = count($this->getAllUsers());
        
        // Kategorienanzahl
        $categoryCount = count($this->getAllCategories());
        
        // Thread-Anzahl abrufen
        $threadCount = $this->model->getThreadCount();
        
        // Beitragsanzahl abrufen
        $postCount = $this->model->getPostCount();
        
        // Neueste Aktivitäten abrufen
        $recentActivity = $this->model->getRecentActivity(10);
        
        return [
            'userCount' => $userCount,
            'categoryCount' => $categoryCount,
            'threadCount' => $threadCount,
            'postCount' => $postCount,
            'recentActivity' => $recentActivity
        ];
    }
    
    public function getAllThreads() {
        $stmt = $this->model->getAllThreads();
        $threads = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $threads[] = [
                'id' => $row['ID'],
                'title' => $row['titel'],
                'author' => $row['author'],
                'category' => $row['category_name'],
                'created_at' => $row['created_at']
            ];
        }
        
        return $threads;
    }
    
    public function getAllPosts() {
        $stmt = $this->model->getAllPosts();
        $posts = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = [
                'id' => $row['ID'],
                'content' => $row['content'],
                'author' => $row['author'],
                'thread' => $row['thread_title'],
                'created_at' => $row['created_at']
            ];
        }
        
        return $posts;
    }
    
    public function updateUser($user_id, $username, $email, $role_id) {
        return $this->model->updateUser($user_id, $username, $email, $role_id);
    }
    
    public function deleteCategory($category_id) {
        return $this->model->deleteCategory($category_id);
    }

    public function updateCategory($category_id, $name, $description) {
        return $this->model->updateCategory($category_id, $name, $description);
    }    
    
    public function deleteThread($thread_id) {
        return $this->model->deleteThread($thread_id);
    }
    
    public function deletePost($post_id) {
        return $this->model->deletePost($post_id);
    }

    public function deleteUser($user_id) {
        return $this->model->deleteUser($user_id);
    }
    
    public function getUserById($user_id) {
        $user = $this->model->getUserById($user_id);
        if ($user) {
            return [
                'id' => $user['ID'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['rules_ID'] == 1 ? 'Admin' : 'User',
                'role_id' => $user['rules_ID']
            ];
        }
        return null;
    }

    public function getCategoryById($category_id) {
        $category = $this->model->getCategoryById($category_id);
        if ($category) {
            return [
                'id' => $category['ID'],
                'name' => $category['name'],
                'description' => $category['description'],
            ];
        }
        return null;
    }
    
    public function createCategory($name, $description) {
        // Validierung auf leere Werte
        if (empty($name) || empty($description)) {
            throw new InvalidArgumentException("Alle Felder müssen ausgefüllt sein");
        }
    
        // Kategorie erstellen
        $categoryId = $this->model->createCategory($name, $description);
    
        return [
            'id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public function getThreadById($thread_id) {
        $thread = $this->model->getThreadById($thread_id);
        
        if (!$thread) {
            return null;
        }
        
        return [
            'id' => $thread['ID'],
            'title' => $thread['titel'],
            'content' => $thread['content'],
            'created_at' => date('d.m.Y, H:i', strtotime($thread['created_at'])) . ' Uhr',
            'user_id' => $thread['users_ID'],
            'category' => $thread['category_name']
        ];
    }
    
    public function getPostById($post_id) {
        $post = $this->model->getPostById($post_id);
        
        if (!$post) {
            return null;
        }
        
        return [
            'id' => $post['ID'],
            'content' => $post['content'],
            'created_at' => date('d.m.Y, H:i', strtotime($post['created_at'])) . ' Uhr',
            'thread_id' => $post['threads_ID'],
            'user_id' => $post['users_ID']
        ];
    }
    
    
    
    

}
?>
