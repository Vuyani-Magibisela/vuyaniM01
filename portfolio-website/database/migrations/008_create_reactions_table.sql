-- Emoji reactions for blog posts
-- One reaction per emoji per IP per post (enforced by UNIQUE KEY)
-- ip_hash stores SHA-256(IP + UA) — no raw PII stored
CREATE TABLE IF NOT EXISTS blog_reactions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    post_id     INT NOT NULL,
    emoji       VARCHAR(20) NOT NULL,
    ip_hash     VARCHAR(64) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (post_id, emoji, ip_hash),
    INDEX idx_post_emoji (post_id, emoji),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);
