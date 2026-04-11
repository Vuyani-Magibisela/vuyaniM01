-- Blog comments with moderation queue
-- All comments start as 'pending' — admin approves before display
-- parent_id reserved for future threaded replies (currently unused)
CREATE TABLE IF NOT EXISTS blog_comments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    post_id      INT NOT NULL,
    parent_id    INT NULL DEFAULT NULL,
    author_name  VARCHAR(100) NOT NULL,
    author_email VARCHAR(255) NOT NULL,
    content      TEXT NOT NULL,
    status       ENUM('pending','approved','spam') DEFAULT 'pending',
    ip_address   VARCHAR(45) NOT NULL,
    user_agent   VARCHAR(500),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post_status (post_id, status),
    INDEX idx_created (created_at),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);
