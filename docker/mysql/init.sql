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
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    threads_ID INT NOT NULL,
    users_ID INT NOT NULL,
    FOREIGN KEY (threads_ID) REFERENCES threads(ID),
    FOREIGN KEY (users_ID) REFERENCES users(ID)
);

INSERT INTO rules (status)
VALUES 
    ('Admin'),
    ('user'),
    ('not loged in');