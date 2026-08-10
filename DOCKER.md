# Local development with Docker

Replaces the old XAMPP/WSL2 setup. Everything the site needs — PHP, Apache,
MariaDB, mail, and a database GUI — runs in containers, so nothing has to be
installed on Windows except Docker Desktop.

## First run

```powershell
cd D:\MyFiles\Projects\Programming\webDev\vuyanimagibisela\vuyaniM01

Copy-Item .env.example .env     # then open .env and set your own passwords
.\dev.ps1 up
```

First build takes a few minutes (compiling PHP extensions and Xdebug).
Subsequent starts are seconds.

| Service | URL | Notes |
|---|---|---|
| Site | http://localhost:8090 | |
| phpMyAdmin | http://localhost:8091 | user `root`, password = `DB_ROOT_PASSWORD` |
| Mailpit | http://localhost:8025 | every outbound email lands here |
| MariaDB | `127.0.0.1:3307` | for DBeaver / TablePlus / VS Code |

## Everyday commands

Both `dev.ps1` (PowerShell) and `make` (Git Bash / WSL) expose the same verbs.

```powershell
.\dev.ps1 up          # build + start
.\dev.ps1 down        # stop and remove containers
.\dev.ps1 logs        # follow Apache + PHP output
.\dev.ps1 shell       # bash inside the app container
.\dev.ps1 db          # mariadb CLI against the dev database
.\dev.ps1 migrate     # run database/run_migrations.php
.\dev.ps1 lint        # php -l over the whole codebase
.\dev.ps1 composer require some/package
.\dev.ps1 reseed      # destroy the DB and re-import the production dump
```

Editing PHP under `portfolio-website/` takes effect immediately — the
directory is bind-mounted and OPcache revalidates on every request. No
restart, no rebuild.

## How this differs from the old setup

**The document root is `public/`.** Apache serves `portfolio-website/public`
directly, which is how a PHP app is supposed to be deployed: `app/`,
`vendor/`, `database/`, and `tools/` sit outside the web root and cannot be
requested over HTTP. Because the app now lives at the site root,
`APP_BASE_URL` is empty locally. On cPanel it stays `/public`, because the
root `.htaccess` redirect is still in play there.

**Configuration is environment-driven.** `app/config/database.php`,
`email.php`, and `config.php` no longer branch on `$_SERVER['HTTP_HOST']` and
no longer contain credentials. They read from the environment via
`App\Core\Env`, with this precedence:

1. Real environment variables (what Compose injects)
2. A `.env` file at the app root or repository root
3. Defaults in code

**Mail goes to Mailpit.** `MAIL_HOST=mailpit` with no auth and no TLS, so
testing the contact form or subscription flow can't send anything to a real
person. Open http://localhost:8025 to read what was "sent."

**MariaDB 10.6, matching production.** Production runs MariaDB 10.6 on cPanel,
not MySQL 8. Same engine locally means SQL that works here works there.

## Database

`docker/mysql/init/10-seed.sql` is the production dump, imported
automatically the first time the `db_data` volume is created. It contains
real contact submissions and subscriber email addresses, so it is
git-ignored and must stay local.

The init script only runs on a *fresh* volume. To reimport after schema
changes:

```powershell
.\dev.ps1 reseed
```

To refresh the snapshot from a newer production export:

```bash
sed -e '/^CREATE DATABASE/d' -e '/^USE `/d' -e 's/`vuyanjcb_vuyanim`\.//g' \
    new-dump.sql > docker/mysql/init/10-seed.sql
```

Migrations `001`–`009` are already applied in the dump. They exist in
`database/migrations/` as the canonical schema history; run
`.\dev.ps1 migrate` after adding a new one.

## Xdebug

Xdebug 3 is installed and set to `start_with_request=trigger`, so it only
attaches when you ask — no slowdown on ordinary requests.

**VS Code** — `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [{
    "name": "Listen for Xdebug",
    "type": "php",
    "request": "launch",
    "port": 9003,
    "pathMappings": { "/var/www/html": "${workspaceFolder}/portfolio-website" }
  }]
}
```

Start the listener, then append `?XDEBUG_TRIGGER=1` to a URL or install a
browser toggle extension.

Turn it off entirely with `XDEBUG_MODE=off` in `.env` and `.\dev.ps1 restart`.

## Deploying

`vendor/` is no longer tracked in Git — `composer.lock` pins PHPMailer 7.0.2,
so the tree is reproducible. On the server:

```bash
composer install --no-dev --optimize-autoloader
```

The server needs its own `.env`, never committed, with `APP_ENV=production`,
`APP_DEBUG=false`, `APP_BASE_URL=/public`, and the real database and SMTP
credentials.

## Security note

The database and SMTP passwords that used to live in `app/config/*.php` were
committed to a public GitHub repository and remain in its history. Anyone who
has ever cloned the repo has them. **Rotate both** in cPanel — the MySQL user
password and the `admin@vuyanimagibisela.co.za` mailbox password — and put
the new values only in the server's `.env`.

Removing them from history as well takes `git filter-repo` and a force-push;
rotating is the part that actually matters.
