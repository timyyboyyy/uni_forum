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

}
?>
