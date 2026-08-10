<#
.SYNOPSIS
    Development helper for the vuyanimagibisela.co.za Docker stack.

.EXAMPLE
    .\dev.ps1 up          # build + start everything
    .\dev.ps1 down        # stop
    .\dev.ps1 shell       # bash inside the app container
    .\dev.ps1 logs        # follow app logs
    .\dev.ps1 db          # mysql client against the dev database
    .\dev.ps1 reseed      # wipe the DB and re-import the production dump
    .\dev.ps1 migrate     # run database/run_migrations.php
    .\dev.ps1 composer install
    .\dev.ps1 lint        # php -l across the codebase
    .\dev.ps1 status      # container health + URLs
#>

param(
    [Parameter(Position = 0)]
    [string]$Command = 'help',

    [Parameter(Position = 1, ValueFromRemainingArguments = $true)]
    [string[]]$Rest
)

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

function Assert-Env {
    if (-not (Test-Path '.env')) {
        Write-Host 'No .env found — creating one from .env.example' -ForegroundColor Yellow
        Copy-Item '.env.example' '.env'
        Write-Host 'Review .env before continuing.' -ForegroundColor Yellow
    }
}

function Show-Urls {
    Write-Host ''
    Write-Host '  Site         http://localhost:8080' -ForegroundColor Green
    Write-Host '  phpMyAdmin   http://localhost:8081   (user: root)' -ForegroundColor Green
    Write-Host '  Mailpit      http://localhost:8025' -ForegroundColor Green
    Write-Host '  MariaDB      127.0.0.1:3307' -ForegroundColor Green
    Write-Host ''
}

switch ($Command.ToLower()) {

    'up' {
        Assert-Env
        docker compose up -d --build
        Write-Host 'Waiting for the database to become healthy...' -ForegroundColor Cyan
        docker compose exec -T db sh -c 'until healthcheck.sh --connect; do sleep 2; done'
        Show-Urls
    }

    'down'    { docker compose down }
    'restart' { docker compose restart app }
    'rebuild' { docker compose up -d --build --force-recreate }
    'stop'    { docker compose stop }
    'status'  { docker compose ps; Show-Urls }
    'logs'    { docker compose logs -f app }
    'shell'   { docker compose exec app bash }

    'db' {
        docker compose exec db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"'
    }

    'reseed' {
        Write-Host 'This destroys the local database and re-imports the production dump.' -ForegroundColor Yellow
        $confirm = Read-Host 'Type YES to continue'
        if ($confirm -ne 'YES') { Write-Host 'Cancelled.'; break }
        docker compose down -v
        docker compose up -d
        Write-Host 'Database recreated from docker/mysql/init/.' -ForegroundColor Green
    }

    'migrate' {
        docker compose exec app php database/run_migrations.php
    }

    'composer' {
        docker compose exec app composer @Rest
    }

    'lint' {
        docker compose exec app sh -c 'find app public tools database -name "*.php" -print0 | xargs -0 -n1 php -l | grep -v "No syntax errors" || echo "All files pass."'
    }

    'test-mail' {
        docker compose exec app php tools/test-email-send.php
        Write-Host 'Check the inbox at http://localhost:8025' -ForegroundColor Green
    }

    default {
        Get-Help $PSCommandPath -Detailed
    }
}
