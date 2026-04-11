-- Add subscribers_notified column to blog_posts table
-- Prevents re-notifying subscribers when editing an already-published post
ALTER TABLE blog_posts ADD COLUMN subscribers_notified TINYINT(1) NOT NULL DEFAULT 0;
