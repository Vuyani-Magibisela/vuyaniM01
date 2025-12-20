# Quick Deployment Checklist

## Pre-Deployment (Already Complete ✓)

- ✅ Security fixes applied
- ✅ Test files deleted
- ✅ Error display disabled
- ✅ XSS vulnerabilities fixed
- ✅ Upload directories protected
- ✅ Production configuration set
- ✅ Deployment package created (production-deploy.zip)

---

## Deployment Steps (Do These Now)

### Step 1: Upload Files (15 minutes)

**File to Upload:** `/var/www/html/vuyaniM01/portfolio-website/production-deploy.zip`

1. Log into cPanel
2. Open File Manager
3. Go to `public_html/` directory
4. Upload `production-deploy.zip`
5. Right-click → Extract
6. Delete the ZIP file

**Verify:** Check that `public_html/` now contains:
- `index.php`
- `.htaccess`
- `app/` directory
- `css/` directory
- `js/` directory
- `images/` directory
- `uploads/` directory

---

### Step 2: Migrate Database (10 minutes)

**File to Upload:** `/var/www/html/vuyaniM01/production_migration.sql`

1. Log into cPanel → phpMyAdmin
2. Select database: `vuyanjcb_vuyanim`
3. Click "Import" tab
4. Upload `production_migration.sql`
5. Click "Go"
6. Wait for success message

**Verify:** Run this SQL query:
```sql
SELECT username, email, role FROM users WHERE username = 'admin';
```

You should see the admin user.

**Full Migration Guide:** See `DATABASE_MIGRATION_GUIDE.md` for detailed instructions

---

### Step 3: Set File Permissions (5 minutes)

**Via cPanel File Manager:**

1. Select `public_html/` directory
2. Right-click → Change Permissions
3. Set directories to `755`
4. Set files to `644`

**Upload directories need `775`:**
- `/public_html/images/blog/uploads/`
- `/public_html/images/projects/uploads/`
- `/public_html/images/projects/gallery/`
- `/public_html/uploads/resources/`

---

### Step 4: Install SSL Certificate (5 minutes)

1. In cPanel → SSL/TLS Status (or Let's Encrypt)
2. Find domain: `vuyanimagibisela.co.za`
3. Click "Issue" or "Install Certificate"
4. Wait for installation (usually 1-2 minutes)

**Verify:** Visit `https://vuyanimagibisela.co.za` (should show padlock icon)

---

### Step 5: Test Website (10 minutes)

#### Test Homepage
- Visit: `https://vuyanimagibisela.co.za`
- ✅ Page loads without errors
- ✅ CSS and images display correctly
- ✅ Navigation menu works

#### Test Admin Login
- Visit: `https://vuyanimagibisela.co.za/auth`
- Login: `admin` / `Admin@2025`
- ✅ Login successful
- ✅ Admin dashboard loads

#### Test Core Pages
- ✅ `/blog` - Blog page loads
- ✅ `/projects` - Projects page loads
- ✅ `/about` - About page loads
- ✅ `/contact` - Contact form loads

#### Test Admin Functions
- ✅ Create a blog post
- ✅ Upload an image
- ✅ Create a project
- ✅ Manage users

---

## Post-Deployment Tasks

### Immediate (Day 1)
- [ ] Change admin password from `Admin@2025` to something secure
- [ ] Add your first blog post
- [ ] Add your projects
- [ ] Update About page content
- [ ] Test contact form submission

### Week 1
- [ ] Add more blog posts
- [ ] Upload project images
- [ ] Set up regular backups
- [ ] Monitor error logs

---

## Important Files & Credentials

### Admin Login
- URL: `https://vuyanimagibisela.co.za/auth`
- Username: `admin`
- Password: `Admin@2025` (change this!)

### Database
- Database: `vuyanjcb_vuyanim`
- User: `vuyanjcb_vuyaniM`
- Password: `=bQw^WUglto@IhRJ`

### Files to Upload
1. `production-deploy.zip` (13MB) - Website files
2. `production_migration.sql` - Database migration

### Documentation Files
- `DEPLOYMENT_INSTRUCTIONS.md` - Complete deployment guide
- `DATABASE_MIGRATION_GUIDE.md` - Database migration steps
- `COMMIT_MESSAGE.md` - Summary of all changes made

---

## Troubleshooting Quick Reference

### Website shows 500 Error
→ Check file permissions (755 for directories, 644 for files)
→ Check `.htaccess` syntax
→ Check error log in cPanel

### Can't login to admin
→ Verify database migration completed
→ Check admin user exists: `SELECT * FROM users WHERE username='admin';`
→ Clear browser cookies

### Images not uploading
→ Check upload directory permissions (should be 775)
→ Verify `.htaccess` exists in upload directories

### Database connection error
→ Verify database name: `vuyanjcb_vuyanim`
→ Verify user: `vuyanjcb_vuyaniM`
→ Check user has ALL PRIVILEGES on database

---

## Success Indicators

✅ Website accessible at https://vuyanimagibisela.co.za
✅ Green padlock (SSL) in browser
✅ Admin login works
✅ No errors in cPanel error log
✅ Can create blog posts
✅ Can upload images
✅ Contact form submits
✅ All pages load correctly

---

## Estimated Total Time

- File upload: 15 minutes
- Database migration: 10 minutes
- Permissions: 5 minutes
- SSL: 5 minutes
- Testing: 10 minutes

**Total: ~45 minutes**

---

## Support Resources

- **Full Deployment Guide:** `DEPLOYMENT_INSTRUCTIONS.md`
- **Database Migration:** `DATABASE_MIGRATION_GUIDE.md`
- **Changes Summary:** `COMMIT_MESSAGE.md`
- **Error Logs:** cPanel → Metrics → Errors

---

**Ready to deploy?** Start with Step 1 and check off each item as you complete it!

**Last Updated:** December 19, 2025
