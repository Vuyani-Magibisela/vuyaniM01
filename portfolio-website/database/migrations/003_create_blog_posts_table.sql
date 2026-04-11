-- Migration: Create blog_posts table
-- Date: 2025-11-09
-- Description: Main blog posts table with all content and metadata

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,

    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    INDEX idx_published_at (published_at),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
