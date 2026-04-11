-- Add profile_image and bio columns to users table
-- Enables admins to manage their author profile image and bio from the dashboard
ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER last_name;
ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER profile_image;
