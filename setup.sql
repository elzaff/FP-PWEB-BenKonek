-- BenKonek Database Setup
-- Run this in phpMyAdmin or: mysql -u root benkonek < setup.sql

CREATE DATABASE IF NOT EXISTS benkonek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE benkonek;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('musician','band','admin') DEFAULT 'musician',
    is_active     BOOLEAN DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS musicians (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNIQUE NOT NULL,
    full_name          VARCHAR(150),
    bio                TEXT,
    location_city      VARCHAR(100),
    primary_instrument VARCHAR(100),
    experience_level   ENUM('Beginner','Intermediate','Advanced','Professional'),
    portfolio_url      VARCHAR(255),
    whatsapp_number    VARCHAR(20),
    photo_profile      VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bands (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNIQUE NOT NULL,
    band_name          VARCHAR(150),
    formation_year     INT,
    main_genre         VARCHAR(100),
    basecamp_location  VARCHAR(150),
    whatsapp_number    VARCHAR(20),
    photo_profile      VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vacancies (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    band_id           INT NOT NULL,
    title             VARCHAR(200) NOT NULL,
    description       TEXT,
    needed_instrument VARCHAR(100),
    project_type      ENUM('Permanent','Session','Recording','Gig'),
    status            ENUM('Open','Closed') DEFAULT 'Open',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (band_id) REFERENCES bands(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Admin account (password: admin123)
INSERT IGNORE INTO users (email, password_hash, role)
VALUES ('admin@benkonek.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
