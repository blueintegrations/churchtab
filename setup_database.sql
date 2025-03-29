-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS churchtab;

-- Create the user if it doesn't exist and grant privileges
CREATE USER IF NOT EXISTS 'churchtab'@'localhost' IDENTIFIED BY 'rm]p1XKjACDNKgfz';
GRANT ALL PRIVILEGES ON churchtab.* TO 'churchtab'@'localhost';
FLUSH PRIVILEGES;

-- Switch to the churchtab database
USE churchtab;

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tabs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255),
    content TEXT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS song_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tab_id INT,
    scheduled_date DATE NOT NULL,
    display_order INT NOT NULL,
    FOREIGN KEY (tab_id) REFERENCES tabs(id)
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add category relationship to tabs if not exists
ALTER TABLE tabs
ADD COLUMN IF NOT EXISTS category_id INT,
ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- Insert default categories if they don't exist
INSERT IGNORE INTO categories (name) VALUES 
('Hymns'),
('Contemporary'),
('Special Music'),
('Seasonal');

-- Create initial admin user if not exists
INSERT IGNORE INTO users (username, password, is_admin) 
VALUES ('admin', '$2y$10$8tPkQAHUC1KfQsGj8kZH3.dUF0lFtGwahqwpyXWTVk2Z71dYaXp4y', TRUE);
