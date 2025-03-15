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
                'name' => $row['name'],
                'topic_count' => $row['topic_count'] ?? 0,
                'last_post_date' => $row['last_post_date'] ?? null,
                'last_post_user' => $row['last_post_user'] ?? 'Niemand'
            ];
        }
        
        return $categories;
    }

}
?>
