-- EventHub database schema
-- Run this in MySQL (phpMyAdmin or CLI) after creating database 'eventhub'

CREATE DATABASE IF NOT EXISTS eventhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eventhub;

-- Users: admin maintains site; users register for events
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    -- New users must be approved by admin before they can log in.
    status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Events: created by admin
CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(64) NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    max_participants INT UNSIGNED NOT NULL,
    -- Limit participants per single registration/team
    max_team_size INT UNSIGNED NOT NULL DEFAULT 1,
    -- Optional image representing the event (uploaded by admin)
    image_path VARCHAR(255) DEFAULT NULL,
    description TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Registrations: one per submission (user can add multiple participants)
CREATE TABLE registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Participants: each registration can have multiple participants
CREATE TABLE participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    INDEX idx_registration (registration_id)
) ENGINE=InnoDB;

-- Gallery images: uploaded by admin, visible to all users
CREATE TABLE gallery_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Store deleted/rejected accounts for audit + messaging at login
CREATE TABLE deleted_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_user_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL,
    status_at_deletion VARCHAR(32) NOT NULL,
    deleted_by_admin_id INT UNSIGNED DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB;

-- Admin activity logs
CREATE TABLE admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(64) NOT NULL,
    target_type VARCHAR(32) DEFAULT NULL,
    target_id INT UNSIGNED DEFAULT NULL,
    meta TEXT DEFAULT NULL,
    ip VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Default admin (password: Admin123!). Skip if already exists.
INSERT IGNORE INTO users (name, email, password_hash, role, status) VALUES
('System Admin', 'admin@eventhub.local', '$2y$10$ozU9ZalX9RHPsbrWEcPvSurtGsqwz7SMFBE732S9vCtxEHndhwZ9i', 'admin', 'active');

-- Optional: seed sample events
INSERT INTO events (title, category, event_date, location, max_participants, description) VALUES
('Tech Innovation Summit 2026', 'Technology', '2026-01-30 09:00:00', 'Online (Zoom)', 300, 'Join industry leaders and innovators to explore the future of technology, including AI, cloud-native architecture, and emerging product trends.'),
('Startup Pitch Night', 'Business', '2026-02-10 18:00:00', 'New York', 120, 'Founders pitch to investors and mentors. Network with entrepreneurs and VCs.'),
('Design Thinking Workshop', 'Workshop', '2026-02-22 10:00:00', 'London', 60, 'Hands-on session to learn user-centered innovation methods and tools.');
