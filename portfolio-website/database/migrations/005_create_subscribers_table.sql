-- Migration: Create subscribers table
-- Date: 2025-12-23
-- Description: Blog newsletter subscribers with double opt-in verification

CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    verification_token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('pending', 'verified', 'unsubscribed') NOT NULL DEFAULT 'pending',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    INDEX idx_email (email),
    INDEX idx_token (verification_token),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
