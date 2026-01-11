-- AplicatieWeb Database Schema
-- Run this to initialize the database

CREATE DATABASE IF NOT EXISTS aplicatieweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aplicatieweb;

-- Users table (admin authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255),
    role ENUM('admin', 'editor') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    content_markdown LONGTEXT,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
    views INT UNSIGNED DEFAULT 0,
    reading_time INT UNSIGNED DEFAULT 0, -- in minutes
    published_at TIMESTAMP NULL,
    scheduled_for TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (title, excerpt, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories/Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Post-Tag relationship
CREATE TABLE IF NOT EXISTS post_tags (
    post_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media library
CREATE TABLE IF NOT EXISTS media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    file_size INT UNSIGNED,
    mime_type VARCHAR(100),
    width INT UNSIGNED, -- for images
    height INT UNSIGNED, -- for images
    alt_text VARCHAR(255),
    caption TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_file_type (file_type),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics (daily aggregated views)
CREATE TABLE IF NOT EXISTS analytics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED,
    date DATE NOT NULL,
    views INT UNSIGNED DEFAULT 1,
    unique_visitors INT UNSIGNED DEFAULT 1,
    UNIQUE KEY unique_post_date (post_id, date),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table (key-value store)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table (optional - for database session storage)
CREATE TABLE IF NOT EXISTS sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED,
    session_data TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123 - CHANGE THIS!)
-- Password hash for 'admin123' using PHP password_hash() with PASSWORD_BCRYPT
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@aplicatieweb.ro', '$2y$10$bvdNX/AfWU9IqTzG2ltHLubLsVf5mbPZIOAo9RJLnIQO.0.lB5vDS', 'Captain Claudiu', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'AplicatieWeb', 'string'),
('site_tagline', 'Developer & Storyteller', 'string'),
('posts_per_page', '10', 'number'),
('analytics_enabled', '1', 'boolean'),
('maintenance_mode', '0', 'boolean')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- =============================================
-- Widgets Table
-- =============================================
CREATE TABLE IF NOT EXISTS widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_key VARCHAR(100) UNIQUE NOT NULL,
    widget_name VARCHAR(255) NOT NULL,
    widget_type VARCHAR(50) NOT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_enabled (is_enabled),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Widgets
INSERT INTO widgets (widget_key, widget_name, widget_type, is_enabled, display_order, settings) VALUES
('autumn_countdown', 'Autumn Countdown', 'seasonal', 1, 1, '{"season": "autumn", "data_file": "data/etape_toamna.json"}'),
('christmas_countdown', 'Christmas Countdown', 'seasonal', 1, 2, '{"season": "christmas", "data_file": "data/etape_craciun.json"}'),
('blog_posts', 'Blog Posts Listing', 'content', 1, 10, '{"posts_per_page": 12, "show_pagination": true}');

-- =============================================
-- Categories Table
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    post_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Categories
INSERT INTO categories (name, slug, description, color) VALUES
('Tehnologie', 'tehnologie', 'Articole despre tehnologie, programare și inovație', '#667eea'),
('Personal', 'personal', 'Gânduri personale și reflecții', '#10b981'),
('Tutorial', 'tutorial', 'Ghiduri și tutoriale pas cu pas', '#f59e0b'),
('Știri', 'stiri', 'Știri și actualizări', '#ef4444'),
('Review', 'review', 'Recenzii de produse și servicii', '#8b5cf6');

-- Update widgets to include category filter
INSERT INTO widgets (widget_key, widget_name, widget_type, is_enabled, display_order, settings) VALUES
('category_filter', 'Category Filter', 'filter', 1, 5, '{"show_post_count": true}');

-- =============================================
-- Reminders Table (Personal Reminders)
-- =============================================
CREATE TABLE IF NOT EXISTS reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NULL,
    is_completed TINYINT(1) DEFAULT 0,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_completed (is_completed),
    INDEX idx_due_date (due_date),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add reminders widget
INSERT INTO widgets (widget_key, widget_name, widget_type, is_enabled, display_order, settings) VALUES
('personal_reminders', 'Personal Reminders', 'personal', 1, 15, '{"show_completed": false, "max_display": 5}')
ON DUPLICATE KEY UPDATE widget_key=widget_key;
