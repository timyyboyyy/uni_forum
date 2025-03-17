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
    

}
?>
