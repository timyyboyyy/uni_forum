<?php
//4-Seite
class DataModel {
    private $conn;
    private $table_name = "ihre_tabelle";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Daten abrufen
    public function getData() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCategories($limit = null) {
      $query = "SELECT
                  c.ID as id,
                  c.name AS 'kategorie',
                  COUNT(DISTINCT t.ID) AS 'themen_anzahl',
                  IFNULL(
                      (
                          SELECT CONCAT(
                              '<a href=\"/thread/?id=', 
                              CASE 
                                  WHEN contrib.ID IS NOT NULL THEN t2.ID
                                  ELSE t3.ID 
                              END,
                              '\">',
                              CASE 
                                  WHEN contrib.ID IS NOT NULL THEN t2.titel
                                  ELSE t3.titel
                              END,
                              '</a><br>',
                              DATE_FORMAT(
                                  CASE 
                                      WHEN contrib.ID IS NOT NULL THEN contrib.created_at
                                      ELSE t3.created_at
                                  END, 
                                  '%d.%m.%Y, %H:%i'
                              ),
                              ' Uhr von ',
                              CASE 
                                  WHEN contrib.ID IS NOT NULL THEN u.username
                                  ELSE u3.username
                              END
                          )
                          FROM (
                              -- Neueste Antworten (contributions)
                              SELECT contrib.ID, contrib.threads_ID, contrib.users_ID, contrib.created_at
                              FROM contributions contrib
                              JOIN threads t2 ON contrib.threads_ID = t2.ID
                              WHERE t2.categories_ID = c.ID
                              
                              UNION ALL
                              
                              -- Neueste Threads (ohne Antworten)
                              SELECT NULL as ID, t3.ID as threads_ID, t3.users_ID, t3.created_at
                              FROM threads t3
                              WHERE t3.categories_ID = c.ID
                              AND NOT EXISTS (
                                  SELECT 1 FROM contributions c2 
                                  WHERE c2.threads_ID = t3.ID
                              )
                          ) as combined_posts
                          LEFT JOIN contributions contrib ON combined_posts.ID = contrib.ID
                          LEFT JOIN threads t2 ON combined_posts.threads_ID = t2.ID
                          LEFT JOIN threads t3 ON combined_posts.threads_ID = t3.ID AND contrib.ID IS NULL
                          LEFT JOIN users u ON contrib.users_ID = u.ID
                          LEFT JOIN users u3 ON combined_posts.users_ID = u3.ID AND contrib.ID IS NULL
                          ORDER BY combined_posts.created_at DESC
                          LIMIT 1
                      ),
                      'Keine Beiträge'
                  ) AS 'letzter_beitrag'
                FROM
                  categories c
                LEFT JOIN
                  threads t ON c.ID = t.categories_ID
                GROUP BY
                  c.ID
                ORDER BY
                  themen_anzahl DESC"; // Sortierung nach Anzahl der Themen (Beliebtheit)
      
      if ($limit !== null) {
          $query .= " LIMIT :limit";
      }
      
      $stmt = $this->conn->prepare($query);
      
      if ($limit !== null) {
          $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      }
      
      $stmt->execute();
      return $stmt;
    }
  
  
  
    
    public function createThread($title, $content, $category_id, $user_id) {
        $query = "INSERT INTO threads (titel, content, created_at, categories_ID, users_ID) 
                  VALUES (:title, :content, NOW(), :category_id, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getLatestPosts($limit = 10) {
        $query = "SELECT 
                    t.ID as thread_id,
                    t.titel as title,
                    c.name as category,
                    u.username as author,
                    t.created_at as date
                  FROM 
                    threads t
                  JOIN 
                    categories c ON t.categories_ID = c.ID
                  JOIN 
                    users u ON t.users_ID = u.ID
                  ORDER BY 
                    t.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getThreadById($thread_id) {
        // Thread-Informationen abrufen
        $query = "SELECT 
                    t.ID, t.titel, t.content, t.created_at,
                    u.username as author,
                    c.name as category_name
                  FROM 
                    threads t
                  JOIN
                    users u ON t.users_ID = u.ID
                  JOIN
                    categories c ON t.categories_ID = c.ID
                  WHERE 
                    t.ID = :thread_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':thread_id', $thread_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getThreadReplies($thread_id) {
        // Antworten zum Thread abrufen
        $query = "SELECT 
                    c.ID, c.content, c.created_at,
                    u.username as author
                  FROM 
                    contributions c
                  JOIN
                    users u ON c.users_ID = u.ID
                  WHERE 
                    c.threads_ID = :thread_id
                  ORDER BY 
                    c.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':thread_id', $thread_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    public function createReply($thread_id, $user_id, $content) {
        $query = "INSERT INTO contributions (content, threads_ID, users_ID) 
                  VALUES (:content, :thread_id, :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':thread_id', $thread_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getThreadsByCategory($category_id) {
      $query = "SELECT 
                  t.ID, t.titel, t.created_at,
                  u.username as author,
                  (SELECT COUNT(*) FROM contributions WHERE threads_ID = t.ID) as reply_count
                FROM 
                  threads t
                JOIN
                  users u ON t.users_ID = u.ID
                WHERE 
                  t.categories_ID = :category_id
                ORDER BY 
                  t.created_at DESC";
      
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':category_id', $category_id);
      $stmt->execute();
      
      return $stmt;
  }

  public function registerUser($username, $email, $password) {
    // Überprüfen, ob Benutzer bereits existiert
    $check_query = "SELECT ID FROM users WHERE username = :username OR email = :email";
    $check_stmt = $this->conn->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Benutzername oder E-Mail existiert bereits'];
    }
    
    // Hash-Wert des Passworts erstellen
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Standardrolle (2 = user) zuweisen
    $rules_id = 2;
    
    // Benutzer erstellen
    $query = "INSERT INTO users (username, email, password_hash, rules_ID) 
              VALUES (:username, :email, :password_hash, :rules_id)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':rules_id', $rules_id);
    
    if($stmt->execute()) {
        return ['success' => true, 'user_id' => $this->conn->lastInsertId()];
    }
    
    return ['success' => false, 'message' => 'Registrierung fehlgeschlagen'];
}

public function loginUser($username, $password) {
    // Benutzer mit Benutzernamen oder E-Mail suchen
    $query = "SELECT ID, username, email, password_hash, rules_ID 
              FROM users 
              WHERE username = :username OR email = :username";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Passwort überprüfen mit password_verify
        if(password_verify($password, $user['password_hash'])) {
            return [
                'success' => true,
                'user' => [
                    'id' => $user['ID'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'rules_id' => $user['rules_ID']
                ]
            ];
        }
    }
    
    return ['success' => false, 'message' => 'Ungültiger Benutzername oder Passwort'];
}

public function getUserProfile($user_id) {
  $query = "SELECT username, email, DATE_FORMAT(created_at, '%d.%m.%Y') as created_at 
            FROM users 
            WHERE ID = :user_id";
  
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':user_id', $user_id);
  $stmt->execute();
  
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getUserActivity($user_id) {
  // Anzahl der erstellten Threads
  $thread_query = "SELECT COUNT(*) as thread_count 
                  FROM threads 
                  WHERE users_ID = :user_id";
  
  $thread_stmt = $this->conn->prepare($thread_query);
  $thread_stmt->bindParam(':user_id', $user_id);
  $thread_stmt->execute();
  $thread_count = $thread_stmt->fetch(PDO::FETCH_ASSOC)['thread_count'];
  
  // Anzahl der Beiträge
  $post_query = "SELECT COUNT(*) as post_count 
                FROM contributions 
                WHERE users_ID = :user_id";
  
  $post_stmt = $this->conn->prepare($post_query);
  $post_stmt->bindParam(':user_id', $user_id);
  $post_stmt->execute();
  $post_count = $post_stmt->fetch(PDO::FETCH_ASSOC)['post_count'];
  
  // Neueste Aktivitäten (Threads und Antworten)
  $activity_query = "SELECT 'Thread erstellt' as type, 
                           t.ID as thread_id, 
                           t.titel as title, 
                           DATE_FORMAT(t.created_at, '%d.%m.%Y, %H:%i') as date
                    FROM threads t 
                    WHERE t.users_ID = :user_id 
                    
                    UNION ALL 
                    
                    SELECT 'Antwort geschrieben' as type, 
                           t.ID as thread_id, 
                           t.titel as title, 
                           DATE_FORMAT(c.created_at, '%d.%m.%Y, %H:%i') as date 
                    FROM contributions c
                    JOIN threads t ON c.threads_ID = t.ID
                    WHERE c.users_ID = :user_id 
                    
                    ORDER BY date DESC 
                    LIMIT 10";
  
  $activity_stmt = $this->conn->prepare($activity_query);
  $activity_stmt->bindParam(':user_id', $user_id);
  $activity_stmt->execute();
  
  $activities = [];
  while ($row = $activity_stmt->fetch(PDO::FETCH_ASSOC)) {
      $activities[] = $row;
  }
  
  return [
      'thread_count' => $thread_count,
      'post_count' => $post_count,
      'recent_activities' => $activities
  ];
}

public function updateUserProfile($user_id, $username, $email) {
  // Prüfen, ob Benutzername oder E-Mail bereits existieren
  $check_query = "SELECT ID FROM users 
                 WHERE (username = :username OR email = :email) 
                 AND ID != :user_id";
  
  $check_stmt = $this->conn->prepare($check_query);
  $check_stmt->bindParam(':username', $username);
  $check_stmt->bindParam(':email', $email);
  $check_stmt->bindParam(':user_id', $user_id);
  $check_stmt->execute();
  
  if ($check_stmt->rowCount() > 0) {
      return ['success' => false, 'message' => 'Benutzername oder E-Mail wird bereits verwendet'];
  }
  
  // Profil aktualisieren
  $update_query = "UPDATE users 
                  SET username = :username, email = :email 
                  WHERE ID = :user_id";
  
  $update_stmt = $this->conn->prepare($update_query);
  $update_stmt->bindParam(':username', $username);
  $update_stmt->bindParam(':email', $email);
  $update_stmt->bindParam(':user_id', $user_id);
  
  if ($update_stmt->execute()) {
      return ['success' => true];
  }
  
  return ['success' => false, 'message' => 'Fehler beim Aktualisieren des Profils'];
}

public function updateUserPassword($user_id, $current_password, $new_password) {
  // Aktuelles Passwort überprüfen
  $check_query = "SELECT password_hash FROM users WHERE ID = :user_id";
  $check_stmt = $this->conn->prepare($check_query);
  $check_stmt->bindParam(':user_id', $user_id);
  $check_stmt->execute();
  
  $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!password_verify($current_password, $user['password_hash'])) {
      return ['success' => false, 'message' => 'Aktuelles Passwort ist falsch'];
  }
  
  // Neues Passwort setzen
  $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
  
  $update_query = "UPDATE users 
                  SET password_hash = :password_hash 
                  WHERE ID = :user_id";
  
  $update_stmt = $this->conn->prepare($update_query);
  $update_stmt->bindParam(':password_hash', $password_hash);
  $update_stmt->bindParam(':user_id', $user_id);
  
  if ($update_stmt->execute()) {
      return ['success' => true];
  }
  
  return ['success' => false, 'message' => 'Fehler beim Aktualisieren des Passworts'];
}

  

}
?>
