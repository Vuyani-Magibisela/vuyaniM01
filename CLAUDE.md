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
Configuration is environment-driven via `App\Core\Env` (`app/core/Env.php`).
There is **no hostname sniffing and there are no credentials in the repo.**
Precedence: real env vars → `.env` file → code defaults. See `.env.example`.

| Setting | Docker | cPanel production |
|---|---|---|
| `APP_BASE_URL` | `` (empty — DocumentRoot is `public/`) | `/public` |
| `APP_DEBUG` | `true` | `false` |
| `DB_HOST` | `db` | `localhost` |
| `MAIL_HOST` | `mailpit` (no auth, no TLS) | `mail.vuyanimagibisela.co.za` (SSL 465) |

## Development Commands

Development runs in Docker. See `DOCKER.md` for the full guide.

```powershell
.\dev.ps1 up          # build + start (site :8090, phpMyAdmin :8091, Mailpit :8025)
.\dev.ps1 shell       # bash inside the app container
.\dev.ps1 db          # mariadb CLI
.\dev.ps1 migrate     # run database/run_migrations.php
.\dev.ps1 lint        # php -l over the codebase
.\dev.ps1 logs        # follow Apache + PHP output
```

`make up`, `make shell`, etc. work identically from Git Bash or WSL.

Never run PHP or MySQL directly on the Windows host — the container is the
only supported environment, and it matches production (PHP 8.3, MariaDB 10.6).

### Composer Dependencies
```bash
.\dev.ps1 composer require some/package
```
`vendor/` is git-ignored; `composer.lock` pins PHPMailer 7.0.2.

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
- Add a numbered file to `database/migrations/`, then `.\dev.ps1 migrate`
- Use prepared statements via `BaseModel` methods
- Credentials come from `.env` — never edit them into `app/config/`

### Things that must not happen
- No credentials, dumps, logs, or uploads committed to Git
- No diagnostic or debug scripts inside `public/` — they belong in `tools/`,
  which is outside the document root
- No manual edits on the production server; deploy from Git

### Responsive Design
- Mobile-first approach implemented
- CSS organized by: base → layout → modules → pages → themes → media queries
- Theme switching handled via JavaScript in `public/js/theme.js`