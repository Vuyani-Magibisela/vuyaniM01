# Repository Guidelines

## Project Structure & Module Organization

This portfolio uses a custom PHP MVC framework with vanilla JavaScript and CSS. Application paths below are relative to `portfolio-website/`:

- `app/controllers/`, `app/models/`, and `app/views/`: request handling, PDO data access, and PHP templates; shared templates live in `views/layouts/` and `views/partials/`.
- `app/core/` and `app/config/`: framework utilities and environment-driven configuration.
- `public/`: Apache document root, entry point, CSS, JavaScript, images, and resources.
- `database/migrations/`: numbered SQL migrations such as `009_create_comments_table.sql`.
- `tools/`: command-line diagnostics, including the email smoke test.

Repository-level `docker/`, `docker-compose.yml`, `dev.ps1`, and `Makefile` define local development. Consult `DOCKER.md` for setup details.

## Build, Test, and Development Commands

Run commands from the repository root. Use Docker's PHP 8.3 and MariaDB 10.6 environment; do not run PHP or MySQL directly on Windows.

- `Copy-Item .env.example .env`: initialize configuration only if `.env` is absent; set local passwords.
- `.\dev.ps1 up`: build and start the stack; the default site URL is `http://localhost:8090`.
- `.\dev.ps1 composer install`: install locked PHP dependencies inside the container.
- `.\dev.ps1 migrate`: apply database migrations.
- `.\dev.ps1 lint`: check PHP syntax; inspect output for errors.
- `.\dev.ps1 test-mail`: send a test email to local Mailpit, normally at `http://localhost:8025`.
- `.\dev.ps1 logs`: follow application logs.

Equivalent Make targets cover common operations. PHP and static asset edits take effect without rebuilding.

## Coding Style & Naming Conventions

Match surrounding formatting; PHP uses four-space indentation. Use PascalCase classes, camelCase methods and variables, and snake_case view filenames. Controllers extend `BaseController`; models extend `BaseModel`. Preserve existing `App\` namespaces and Composer autoload mappings. Use PDO prepared statements. Keep theme styles in `public/css/themes/`. No dedicated formatter is configured.

## Testing Guidelines

No automated test framework or coverage threshold is configured. Run PHP lint and manually exercise affected routes, forms, and admin flows. Check desktop/mobile layouts and both themes for UI changes. Verify email behavior in Mailpit. Record checks and results in the pull request.

## Commit & Pull Request Guidelines

Recent commits use descriptive plain-language subjects; no consistent Conventional Commits scheme exists. Use focused imperative subjects and feature branches. Pull requests should explain behavior changes, link relevant issues, document migration/configuration changes, and include screenshots for UI updates.

## Security & Configuration

Keep credentials in `.env`. Never commit secrets, database dumps, logs, uploads, or `vendor/`. Keep diagnostic scripts outside `public/`. Treat `reseed` as destructive: it deletes the local database volume.
