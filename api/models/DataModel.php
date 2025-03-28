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
                  c.description AS 'beschreibung',
                  c.created_at AS 'erstellt_am',
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
                    t.ID, t.titel, t.content, t.created_at, t.users_ID, t.categories_ID,
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
  // DataModel.php
  $activity_query = "SELECT 'Thread erstellt' as type, 
                            t.ID as thread_id, 
                            NULL as post_id,
                            t.titel as title, 
                            DATE_FORMAT(t.created_at, '%d.%m.%Y, %H:%i') as date
                      FROM threads t 
                      WHERE t.users_ID = :user_id 

                      UNION ALL 

                      SELECT 'Antwort geschrieben' as type, 
                            t.ID as thread_id, 
                            c.ID as post_id,
                            SUBSTRING(c.content, 1, 30) as preview, 
                            DATE_FORMAT(c.created_at, '%d.%m.%Y, %H:%i') as date 
                      FROM contributions c
                      JOIN threads t ON c.threads_ID = t.ID
                      WHERE c.users_ID = :user_id 

                      ORDER BY date DESC";

  
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

public function getAllUsers() {
  $query = "SELECT ID, username, email, rules_ID, created_at FROM users ORDER BY ID";
  $stmt = $this->conn->prepare($query);
  $stmt->execute();
  return $stmt;
}

public function deleteUser($user_id) {
    $this->conn->beginTransaction();
    
    try {
        // Lösche alle Beiträge des Benutzers
        $stmt_posts = $this->conn->prepare("
            DELETE FROM contributions 
            WHERE users_ID = :user_id
        ");
        $stmt_posts->execute([':user_id' => $user_id]);

        // Lösche alle Threads des Benutzers
        $stmt_threads = $this->conn->prepare("
            DELETE FROM threads 
            WHERE users_ID = :user_id
        ");
        $stmt_threads->execute([':user_id' => $user_id]);

        // Lösche den Benutzer selbst
        $stmt_user = $this->conn->prepare("
            DELETE FROM users 
            WHERE ID = :user_id
        ");
        $stmt_user->execute([':user_id' => $user_id]);

        // Überprüfe ob Benutzer existiert hat
        if ($stmt_user->rowCount() === 0) {
            throw new RuntimeException('Benutzer existiert nicht', 404);
        }

        $this->conn->commit();
        return true;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Delete User Error: " . $e->getMessage());
        return false;
    }
}


public function getThreadCount() {
  $query = "SELECT COUNT(*) as count FROM threads";
  $stmt = $this->conn->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['count'];
}

public function getPostCount() {
  $query = "SELECT COUNT(*) as count FROM contributions";
  $stmt = $this->conn->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['count'];
}

public function getRecentActivity($limit = 10) {
  $query = "
  (SELECT 
      'Neuer Thread' as action, 
      u.username as user, 
      DATE_FORMAT(t.created_at, '%d.%m.%Y, %H:%i') as date,
      t.titel as details
   FROM threads t
   JOIN users u ON t.users_ID = u.ID
   ORDER BY t.created_at DESC
   LIMIT :half_limit)
  
  UNION ALL
  
  (SELECT 
      'Neue Antwort' as action, 
      u.username as user, 
      DATE_FORMAT(c.created_at, '%d.%m.%Y, %H:%i') as date,
      CONCAT('Re: ', t.titel) as details
   FROM contributions c
   JOIN users u ON c.users_ID = u.ID
   JOIN threads t ON c.threads_ID = t.ID
   ORDER BY c.created_at DESC
   LIMIT :half_limit)
   
   ORDER BY date DESC
   LIMIT :limit";
  
  $half_limit = intval($limit / 2);
  
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':half_limit', $half_limit, PDO::PARAM_INT);
  $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  $stmt->execute();
  
  $activities = [];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $activities[] = $row;
  }
  
  return $activities;
}

public function getAllThreads() {
  $query = "SELECT 
              t.ID, t.titel, t.created_at,
              u.username as author,
              c.name as category_name
            FROM threads t
            JOIN users u ON t.users_ID = u.ID
            JOIN categories c ON t.categories_ID = c.ID
            ORDER BY t.created_at DESC";
  
  $stmt = $this->conn->prepare($query);
  $stmt->execute();
  return $stmt;
}

public function getAllPosts() {
  $query = "SELECT 
              c.ID, c.content, c.created_at,
              u.username as author,
              t.titel as thread_title
            FROM contributions c
            JOIN users u ON c.users_ID = u.ID
            JOIN threads t ON c.threads_ID = t.ID
            ORDER BY c.created_at DESC";
  
  $stmt = $this->conn->prepare($query);
  $stmt->execute();
  return $stmt;
}

public function updateUser($user_id, $username, $email, $role_id) {
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
  
  // Benutzer aktualisieren
  $update_query = "UPDATE users 
                  SET username = :username, email = :email, rules_ID = :role_id 
                  WHERE ID = :user_id";
  
  $update_stmt = $this->conn->prepare($update_query);
  $update_stmt->bindParam(':username', $username);
  $update_stmt->bindParam(':email', $email);
  $update_stmt->bindParam(':role_id', $role_id);
  $update_stmt->bindParam(':user_id', $user_id);
  
  if ($update_stmt->execute()) {
      return ['success' => true];
  }
  
  return ['success' => false, 'message' => 'Fehler beim Aktualisieren des Benutzers'];
}

public function deleteCategory($category_id) {
  try {
      // Transaktion starten
      $this->conn->beginTransaction();
      
      // Alle Antworten zu Threads dieser Kategorie löschen
      $delete_replies = "DELETE FROM contributions 
                        WHERE threads_ID IN (SELECT ID FROM threads WHERE categories_ID = :category_id)";
      $stmt_replies = $this->conn->prepare($delete_replies);
      $stmt_replies->bindParam(':category_id', $category_id);
      $stmt_replies->execute();
      
      // Alle Threads dieser Kategorie löschen
      $delete_threads = "DELETE FROM threads WHERE categories_ID = :category_id";
      $stmt_threads = $this->conn->prepare($delete_threads);
      $stmt_threads->bindParam(':category_id', $category_id);
      $stmt_threads->execute();
      
      // Kategorie löschen
      $delete_category = "DELETE FROM categories WHERE ID = :category_id";
      $stmt_category = $this->conn->prepare($delete_category);
      $stmt_category->bindParam(':category_id', $category_id);
      $stmt_category->execute();
      
      // Transaktion abschließen
      $this->conn->commit();
      return true;
  } catch (Exception $e) {
      // Bei Fehler: Rollback
      $this->conn->rollBack();
      return false;
  }
}

public function deletePost($post_id) {
  $query = "DELETE FROM contributions WHERE ID = :post_id";
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':post_id', $post_id);
  return $stmt->execute();
}



public function getUserById($user_id) {
  $query = "SELECT ID, username, email, rules_ID FROM users WHERE ID = :user_id";
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':user_id', $user_id);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getCategoryById($category_id) {
  $query = "SELECT ID, name, description FROM categories WHERE ID = :category_id";
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':category_id', $category_id);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function updateCategory($category_id, $name, $description) {
  // Prüfen, ob der Kategoriename bereits existiert
  $check_query = "SELECT ID FROM categories 
                  WHERE name = :name AND ID != :category_id";
  
  $check_stmt = $this->conn->prepare($check_query);
  $check_stmt->bindParam(':name', $name);
  $check_stmt->bindParam(':category_id', $category_id);
  $check_stmt->execute();
  
  if ($check_stmt->rowCount() > 0) {
      return ['success' => false, 'message' => 'Kategoriename existiert bereits'];
  }
  
  // Kategorie aktualisieren
  $update_query = "UPDATE categories 
                   SET name = :name, description = :description 
                   WHERE ID = :category_id";
  
  $update_stmt = $this->conn->prepare($update_query);
  $update_stmt->bindParam(':name', $name);
  $update_stmt->bindParam(':description', $description);
  $update_stmt->bindParam(':category_id', $category_id);
  
  if ($update_stmt->execute()) {
      return ['success' => true, 'message' => 'Kategorie erfolgreich aktualisiert'];
  }
  
  return ['success' => false, 'message' => 'Fehler beim Aktualisieren der Kategorie'];
}

public function createCategory($name, $description) {
  $this->conn->beginTransaction();
  
  try {
      // Duplikatprüfung
      $check = $this->conn->prepare("SELECT id FROM categories WHERE name = ?");
      $check->execute([$name]);
      
      if ($check->rowCount() > 0) {
          throw new RuntimeException("Kategoriename existiert bereits");
      }

      // Insert-Query mit Prepared Statement
      $stmt = $this->conn->prepare("
          INSERT INTO categories (name, description, created_at)
          VALUES (?, ?, NOW())
      ");
      
      $stmt->execute([$name, $description]);
      $categoryId = $this->conn->lastInsertId();
      
      $this->conn->commit();
      return $categoryId;

  } catch (PDOException $e) {
      $this->conn->rollBack();
      error_log("Kategorie-Erstellungsfehler: " . $e->getMessage());
      throw new RuntimeException("Datenbankfehler beim Anlegen der Kategorie");
  }
}

public function deleteThread($thread_id) {
  try {
      // Transaktion starten
      $this->conn->beginTransaction();
      
      // Zuerst alle Antworten (contributions) zum Thread löschen
      $delete_contributions = "DELETE FROM contributions WHERE threads_ID = :thread_id";
      $stmt_contributions = $this->conn->prepare($delete_contributions);
      $stmt_contributions->bindParam(':thread_id', $thread_id);
      $stmt_contributions->execute();
      
      // Dann den Thread selbst löschen
      $delete_thread = "DELETE FROM threads WHERE ID = :thread_id";
      $stmt_thread = $this->conn->prepare($delete_thread);
      $stmt_thread->bindParam(':thread_id', $thread_id);
      $stmt_thread->execute();
      
      // Transaktion abschließen
      $this->conn->commit();
      return true;
  } catch (Exception $e) {
      // Bei Fehler: Rollback
      $this->conn->rollBack();
      return false;
  }
}

public function getPostById($post_id) {
  $query = "SELECT *
            FROM contributions 
            WHERE ID = :post_id";
  
  $stmt = $this->conn->prepare($query);
  $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
  $stmt->execute();
  
  return $stmt->fetch(PDO::FETCH_ASSOC);
}




}
?>
