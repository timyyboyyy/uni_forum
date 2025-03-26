-- Beispiel-Daten für 'categories'
INSERT INTO categories (name, description)
VALUES
('General Discussion', 'A place for general discussions about various topics.'),
('Announcements', 'Official announcements and updates from the forum team.'),
('Feedback', 'Share your feedback and suggestions for the forum.');


-- Anlegen eine Admin Kontos    --Einlogdaten: Username: admin  Password:admin
INSERT INTO users(password_hash, email, username, rules_ID) 
VALUES
('$2y$10$C680Di96Al4GKmshXxAgtOm76w/ko9obeAZyj0PN5ILPkFG1I9iu.','admin@admin.de','admin',1);