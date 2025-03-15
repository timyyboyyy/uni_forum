-- Beispiel-Daten für 'users'
INSERT INTO users (username, email, password_hash, rules_ID)
VALUES
('admin', 'admin@example.com', 'hashed_password_1', 1),
('user1', 'user1@example.com', 'hashed_password_2', 2),
('user2', 'user2@example.com', 'hashed_password_3', 2);

-- Beispiel-Daten für 'categories'
INSERT INTO categories (name, description)
VALUES
('General Discussion', 'A place for general discussions about various topics.'),
('Announcements', 'Official announcements and updates from the forum team.'),
('Feedback', 'Share your feedback and suggestions for the forum.');

-- Beispiel-Daten für 'threads'
INSERT INTO threads (titel, content, users_ID, categories_ID)
VALUES
('Welcome to the Forum!', 'This is the first thread in the General Discussion category.', 1, 1),
('Forum Rules and Guidelines', 'Please read the rules before posting.', 1, 2),
('Feedback Thread', 'Let us know how we can improve!', 2, 3);

-- Beispiel-Daten für 'contributions'
INSERT INTO contributions (content, threads_ID, users_ID)
VALUES
('Thank you for setting this up!', 1, 2),
('Noted! I will follow the rules.', 2, 3),
('I think the forum could use a dark mode.', 3, 2);
