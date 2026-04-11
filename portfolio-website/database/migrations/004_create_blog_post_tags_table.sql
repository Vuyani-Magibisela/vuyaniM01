-- Migration: Create blog_post_tags junction table
-- Date: 2025-11-09
-- Description: Many-to-many relationship between blog_posts and tags

CREATE TABLE IF NOT EXISTS blog_post_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,

    UNIQUE KEY unique_post_tag (post_id, tag_id),
    INDEX idx_post (post_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
