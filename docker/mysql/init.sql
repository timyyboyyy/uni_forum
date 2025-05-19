USE forum_db;

CREATE TABLE rules(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR (255) UNIQUE NOT NULL
);

CREATE TABLE users(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR (255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    rules_ID INT NOT NULL,
    FOREIGN KEY (rules_ID) REFERENCES rules(ID)
);

CREATE TABLE categories(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR (255) UNIQUE NOT NULL,
    description VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE threads(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    titel VARCHAR (1000) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    users_ID INT NOT NULL,
    categories_ID INT NOT NULL,
    FOREIGN KEY (users_ID) REFERENCES users(ID),
    FOREIGN KEY (categories_ID) REFERENCES categories(ID)
);

CREATE TABLE contributions(
    ID INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    threads_ID INT NOT NULL,
    users_ID INT NOT NULL,
    FOREIGN KEY (threads_ID) REFERENCES threads(ID),
    FOREIGN KEY (users_ID) REFERENCES users(ID)
);

INSERT INTO rules (status)
VALUES 
    ('Admin'),
    ('user');


INSERT INTO categories (name, description)
VALUES
('Technology', 'Discussions about the latest technology trends and gadgets.'),
('Gaming', 'Talk about video games, consoles, and gaming news.'),
('Off-Topic', 'Anything that does not fit in other categories.');

-- Beispiel-Daten für 'users'
INSERT INTO users (password_hash, email, username, rules_ID) 
VALUES
('$2y$10$C680Di96Al4GKmshXxAgtOm76w/ko9obeAZyj0PN5ILPkFG1I9iu.','admin@admin.de','admin',1),
('$2y$10$4f0Jm8TnOWQeP1FfD9k34uMh3Y1H2T8PZ8NC6vB8BQQeMOkTtVpQm', 'user1@example.com', 'TechFan42', 2),
('$2y$10$kdj3ls9YlFkP2Xg4NR8YJe.QPdP7Zc5l43pNp8R6yx/D6flv3UI8m', 'user2@example.com', 'GamerX99', 2),
('$2y$10$7LmF9TpOMdsu1rGJc/dBDeMFyozF8yT/Oz/GZ6T/D/BwX5RjUKN1K', 'user3@example.com', 'RandomDude', 2);

-- Beispiel-Daten für 'threads'
INSERT INTO threads (titel, content, users_ID, categories_ID)
VALUES
('What is your favorite programming language?', 'I personally love Python. What about you?', 1, 1),
('Best gaming console in 2025?', 'Should I get the PS6 or the new Xbox? Need opinions!', 2, 2),
('Random Chat Thread', 'Just talk about whatever comes to mind!', 3, 3);

-- Beispiel-Daten für 'contributions'
INSERT INTO contributions (content, threads_ID, users_ID)
VALUES
('I think Python is great for beginners and pros alike.', 1, 2),
('I prefer JavaScript, because it is so versatile.', 1, 3),
('PS6 all the way! The exclusive games are amazing.', 2, 1),
('Xbox has better performance, but I prefer PC gaming.', 2, 3),
('Hello everyone! Just dropping by to say hi.', 3, 1);
