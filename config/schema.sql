CREATE DATABASE IF NOT EXISTS churchtab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE churchtab;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE tabs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255),
    content MEDIUMTEXT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE song_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tab_id INT,
    scheduled_date DATE NOT NULL,
    display_order INT NOT NULL,
    FOREIGN KEY (tab_id) REFERENCES tabs(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create initial admin user
-- Default password is 'admin123' (you should change this immediately after first login)
INSERT INTO users (username, password, is_admin) 
VALUES ('admin', '$2y$10$8tPkQAHUC1KfQsGj8kZH3.dUF0lFtGwahqwpyXWTVk2Z71dYaXp4y', TRUE);

-- Create some example categories for organization (optional)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add category relationship to tabs
ALTER TABLE tabs
ADD COLUMN category_id INT,
ADD COLUMN key_signature VARCHAR(10),
ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- Insert some default categories
INSERT INTO categories (name) VALUES 
('Hymns'),
('Contemporary'),
('Special Music'),
('Seasonal');
