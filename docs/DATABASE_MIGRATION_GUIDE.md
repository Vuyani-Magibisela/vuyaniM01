# Production Database Migration Guide

## Overview

Your production database `vuyanjcb_vuyanim` already exists with the basic structure, but needs updates to match your local development environment. This guide will walk you through the migration process step-by-step.

## What This Migration Does

### 1. Updates Users Table
- Adds `is_active` column (to enable/disable users)
- Adds `email_verified_at` column (for email verification)
- Adds `remember_token` column (for "Remember Me" functionality)

### 2. Updates Projects Table
- Adds `status` column (draft/published)
- Adds `author_id` column (links project to user)
- Adds `published_at` timestamp

### 3. Character Encoding
- Converts all 14 tables from `latin1_swedish_ci` to `utf8mb4_unicode_ci`
- Ensures proper support for international characters and emojis

### 4. Creates Admin User
- Username: `admin`
- Password: `Admin@2025`
- Email: `admin@vuyanimagibisela.co.za`
- Role: `admin`

---

## Step-by-Step Migration Instructions

### Step 1: Access cPanel

1. Log into your hosting cPanel
2. Find and click **phpMyAdmin** (usually under "Databases" section)

### Step 2: Select Database

1. In phpMyAdmin, look at the left sidebar
2. Click on database: **`vuyanjcb_vuyanim`**
3. You should see all your tables listed (blog_categories, blog_posts, users, etc.)

### Step 3: Backup Current Database (Important!)

**Before making any changes, create a backup:**

1. Click on your database name in the left sidebar
2. Click the **"Export"** tab at the top
3. Keep "Quick" method selected
4. Click **"Go"** button
5. Save the `.sql` file to your computer

This backup allows you to restore if anything goes wrong.

### Step 4: Run Migration Script

#### Option A: Upload SQL File (Recommended)

1. In phpMyAdmin, with your database selected, click the **"Import"** tab
2. Click **"Choose File"** button
3. Upload the file: `production_migration.sql` (located in `/var/www/html/vuyaniM01/`)
4. Leave format as "SQL"
5. Click **"Go"** button at the bottom
6. Wait for success message

#### Option B: Copy-Paste SQL (Alternative)

1. Open the file `/var/www/html/vuyaniM01/production_migration.sql`
2. Copy all the SQL content
3. In phpMyAdmin, click the **"SQL"** tab
4. Paste the SQL into the text box
5. Click **"Go"** button
6. Wait for success message

### Step 5: Verify Migration Success

After running the migration, verify the changes:

#### Check Users Table:
```sql
DESCRIBE users;
```

You should see these columns:
- `id`
- `username`
- `email`
- `password`
- `first_name`
- `last_name`
- `role`
- **`is_active`** ← NEW
- **`email_verified_at`** ← NEW
- **`remember_token`** ← NEW
- `created_at`
- `updated_at`

#### Check Projects Table:
```sql
DESCRIBE projects;
```

You should see these new columns:
- **`status`** ← NEW
- **`author_id`** ← NEW
- **`published_at`** ← NEW

#### Check Admin User:
```sql
SELECT username, email, role, is_active FROM users WHERE username = 'admin';
```

You should see:
```
username | email                          | role  | is_active
---------|--------------------------------|-------|----------
admin    | admin@vuyanimagibisela.co.za   | admin | 1
```

#### Check Character Encoding:
```sql
SHOW TABLE STATUS WHERE Name = 'users';
```

Look for `Collation` column - should show: `utf8mb4_unicode_ci`

---

## Troubleshooting

### Error: "Duplicate column name 'is_active'"

**Cause:** Column already exists (migration already run)

**Solution:** Skip to Step 5 to verify, or restore from backup and try again

### Error: "Duplicate entry 'admin' for key 'username'"

**Cause:** Admin user already exists

**Solution:** This is OK! Skip the user creation part. You can update the password:
```sql
UPDATE users
SET password = '$2y$12$XtLfw4dGQBUCAtvkFaKRGOaSf3rI/mdYoadxgwRUEkh4QxYS9/ClK'
WHERE username = 'admin';
```

### Error: "Foreign key constraint fails"

**Cause:** Issue with adding foreign key for projects.author_id

**Solution:** Run this separately after ensuring users table has data:
```sql
ALTER TABLE projects
ADD CONSTRAINT fk_projects_author
FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL;
```

### Migration Runs But Can't Login

**Check:**
1. Admin user exists: `SELECT * FROM users WHERE username = 'admin';`
2. Password hash is correct
3. is_active = 1
4. Clear browser cache and cookies

---

## Post-Migration Testing

### Test 1: Admin Login

1. Go to: `https://vuyanimagibisela.co.za/auth`
2. Login with:
   - Username: `admin`
   - Password: `Admin@2025`
3. You should be redirected to admin dashboard

### Test 2: Create Blog Post

1. In admin panel, go to **Blog** → **Create New Post**
2. Add a test blog post
3. Upload an image
4. Publish it
5. Verify it appears on the blog page

### Test 3: Create Project

1. In admin panel, go to **Projects** → **Create New Project**
2. Add a test project
3. Upload images
4. Set status to "Published"
5. Verify it appears on projects page

### Test 4: User Management

1. In admin panel, go to **Users**
2. Try creating a new user
3. Try updating user details
4. Try resetting a password

All these should work without errors.

---

## Migration Rollback (If Needed)

If something goes wrong, you can restore your backup:

1. In phpMyAdmin, select database `vuyanjcb_vuyanim`
2. Click **"Import"** tab
3. Upload your backup `.sql` file
4. Check "Enable foreign key checks"
5. Click "Go"
6. Database will be restored to pre-migration state

---

## Security Recommendations

After successful migration:

1. **Change Admin Password**
   - Login to admin panel
   - Go to Users → Edit Admin User
   - Set a strong, unique password
   - Different from `Admin@2025`

2. **Create Additional Users**
   - Create separate user accounts for different team members
   - Don't share the admin account

3. **Regular Backups**
   - Set up automated database backups in cPanel
   - Or manually backup weekly

---

## Migration Checklist

- [ ] Backup current database
- [ ] Run migration SQL in phpMyAdmin
- [ ] Verify users table has new columns
- [ ] Verify projects table has new columns
- [ ] Verify admin user created
- [ ] Verify UTF-8 encoding applied
- [ ] Test admin login
- [ ] Test blog post creation
- [ ] Test project creation
- [ ] Test user management
- [ ] Change admin password
- [ ] Create new backup of migrated database

---

## Summary

**Before Migration:**
- 14 tables with basic structure
- Latin1 character encoding
- Missing columns in users and projects tables
- No admin user

**After Migration:**
- All tables updated with required columns
- UTF-8 MB4 character encoding (full Unicode support)
- Admin user created and ready to use
- Foreign key constraints properly set
- Ready for production use

**Time Required:** 5-10 minutes

---

## Need Help?

If you encounter any issues:

1. Check the troubleshooting section above
2. Verify your backup exists before retrying
3. Check phpMyAdmin error messages for specific details
4. Restore from backup if needed and try again

**Migration File Location:** `/var/www/html/vuyaniM01/production_migration.sql`

---

**Last Updated:** December 19, 2025
