# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Architecture

This is a custom PHP MVC portfolio website with the following key architectural components:

### MVC Structure
- **Controllers** (`app/controllers/`): Handle HTTP requests and business logic
  - All controllers extend `BaseController` and follow naming pattern `{Name}Controller`
  - Methods map to routes: `/controller/method/params`
- **Models** (`app/models/`): Data layer using PDO with MySQL
  - All models extend `BaseModel` which provides database utilities
- **Views** (`app/views/`): PHP templates organized by controller
  - Main layout in `layouts/main.php`, admin layout in `layouts/admin.php`
  - Partials in `partials/` for reusable components

### Core Framework Components
- **Router** (`app/core/Router.php`): URL routing via `.htaccess` rewrite to `index.php?url=`
- **App** (`app/core/App.php`): Controller loader and dispatcher
- **Database** (`app/core/Database.php`): PDO wrapper with connection management
- **Helpers** (`app/core/Helpers.php`): Utility functions

### Environment Configuration
- **Local development**: Uses `/vuyaniM01/portfolio-website/public` base URL
- **Production**: Uses `/public` base URL with automatic environment detection
- Database credentials auto-switch based on hostname detection

## Development Commands

This project uses PHP without a build system. Common development tasks:

### Local Development Setup
```bash
# Start local server (from portfolio-website/public/)
php -S localhost:8000

# Or use XAMPP/WAMP pointing to public/ directory
```

### Database Management
```bash
# Connect to local MySQL
mysql -u vuksDev -p vuyanim

# Test database connection
php public/test-db.php
```

### Composer Dependencies
```bash
# Install dependencies (minimal composer.json for PSR-4 autoloading)
composer install
```

## Key Technical Details

### Routing System
- URLs follow pattern: `/controller/method/param1/param2`
- Default route: `home/index`
- All requests go through `public/index.php` via `.htaccess` rewrite

### Database Access
- Uses PDO with prepared statements
- Database config in `app/config/database.php` with environment detection
- Connection handled through `Database` core class

### Frontend Assets
- CSS: `public/css/` with theme support (light/dark modes in `themes/`)
- JavaScript: `public/js/` with component-based organization
- Images: `public/images/` with favicon support

### Authentication & Admin
- Admin authentication via `AuthController`
- Session management (currently commented out in index.php)
- Admin views in `views/admin/`

## File Structure Context
- Entry point: `portfolio-website/public/index.php`
- Configuration: `app/config/` (database.php, config.php)
- Core framework: `app/core/`
- Application logic: `app/controllers/`, `app/models/`, `app/views/`
- Public assets: `public/css/`, `public/js/`, `public/images/`

## Development Guidelines

### Adding New Features
1. Create controller in `app/controllers/` extending `BaseController`
2. Create corresponding views in `app/views/{controller}/`
3. Add model in `app/models/` if database interaction needed
4. Update CSS in `public/css/` following existing organization

### Database Changes
- Update both local and production database configurations
- Use prepared statements via `BaseModel` methods
- Environment-specific credentials handled automatically

### Responsive Design
- Mobile-first approach implemented
- CSS organized by: base → layout → modules → pages → themes → media queries
- Theme switching handled via JavaScript in `public/js/theme.js`