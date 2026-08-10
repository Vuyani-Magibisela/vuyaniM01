# Development helper (Git Bash / WSL / macOS). Windows users: see dev.ps1
.PHONY: up down restart rebuild logs shell db migrate composer lint status reseed

up:
	@test -f .env || cp .env.example .env
	docker compose up -d --build
	@echo ""
	@echo "  Site         http://localhost:8080"
	@echo "  phpMyAdmin   http://localhost:8081"
	@echo "  Mailpit      http://localhost:8025"
	@echo "  MariaDB      127.0.0.1:3307"

down:      ; docker compose down
stop:      ; docker compose stop
restart:   ; docker compose restart app
rebuild:   ; docker compose up -d --build --force-recreate
status:    ; docker compose ps
logs:      ; docker compose logs -f app
shell:     ; docker compose exec app bash
db:        ; docker compose exec db sh -c 'mariadb -u root -p"$$MARIADB_ROOT_PASSWORD" "$$MARIADB_DATABASE"'
migrate:   ; docker compose exec app php database/run_migrations.php
composer:  ; docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

lint:
	docker compose exec app sh -c 'find app public tools database -name "*.php" -print0 | xargs -0 -n1 php -l | grep -v "No syntax errors" || echo "All files pass."'

reseed:
	@echo "This destroys the local database volume."
	@read -p "Type YES to continue: " c; [ "$$c" = "YES" ] || exit 1
	docker compose down -v
	docker compose up -d
