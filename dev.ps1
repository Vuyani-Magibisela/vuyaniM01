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

function Get-EnvValue {
    param([string]$Key, [string]$Default)
    if (Test-Path '.env') {
        $line = Select-String -Path '.env' -Pattern "^\s*$Key\s*=" | Select-Object -First 1
        if ($line) {
            $v = ($line.Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
            if ($v) { return $v }
        }
    }
    return $Default
}

function Show-Urls {
    $app  = Get-EnvValue 'APP_PORT'        '8090'
    $pma  = Get-EnvValue 'PMA_PORT'        '8091'
    $mail = Get-EnvValue 'MAILPIT_UI_PORT' '8025'
    $db   = Get-EnvValue 'DB_HOST_PORT'    '3307'

    Write-Host ''
    Write-Host "  Site         http://localhost:$app"  -ForegroundColor Green
    Write-Host "  phpMyAdmin   http://localhost:$pma   (user: root)" -ForegroundColor Green
    Write-Host "  Mailpit      http://localhost:$mail" -ForegroundColor Green
    Write-Host "  MariaDB      127.0.0.1:$db"          -ForegroundColor Green
    Write-Host ''
}

# Refuses to start if another container or process already holds one of our
# host ports. Cheaper than debugging a half-started stack.
function Assert-PortsFree {
    $wanted = [ordered]@{
        'app (Apache)' = Get-EnvValue 'APP_PORT'          '8090'
        'phpMyAdmin'   = Get-EnvValue 'PMA_PORT'          '8091'
        'Mailpit UI'   = Get-EnvValue 'MAILPIT_UI_PORT'   '8025'
        'Mailpit SMTP' = Get-EnvValue 'MAILPIT_SMTP_PORT' '1025'
        'MariaDB'      = Get-EnvValue 'DB_HOST_PORT'      '3307'
    }

    # Host ports published by any *other* running container.
    $taken = @{}
    try {
        docker ps --format '{{.Names}}\t{{.Ports}}' 2>$null | ForEach-Object {
            $name, $ports = $_ -split "`t", 2
            if ($name -like 'vuyanim_*') { return }
            foreach ($m in [regex]::Matches($ports, ':(\d+)->')) {
                $taken[$m.Groups[1].Value] = $name
            }
        }
    } catch { }

    $clashes = @()
    foreach ($k in $wanted.Keys) {
        $p = $wanted[$k]

        if ($taken.ContainsKey($p)) {
            $clashes += "  $p  ($k)  -> already published by container '$($taken[$p])'"
            continue
        }

        $listener = Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue
        if ($listener) {
            $owner = (Get-Process -Id $listener[0].OwningProcess -ErrorAction SilentlyContinue).ProcessName
            if ($owner -notmatch 'com\.docker|vpnkit|Docker') {
                $clashes += "  $p  ($k)  -> in use by process '$owner'"
            }
        }
    }

    if ($clashes.Count) {
        Write-Host ''
        Write-Host 'Port conflict — not starting:' -ForegroundColor Red
        $clashes | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        Write-Host ''
        Write-Host 'Change the port in .env and run again. Known users on this' -ForegroundColor Yellow
        Write-Host 'machine: sci-bono 8080/8081/3306, v3 8001/8082/5174/3308,' -ForegroundColor Yellow
        Write-Host 'v2 8000/5050/5173/5433.' -ForegroundColor Yellow
        Write-Host ''
        exit 1
    }
}

switch ($Command.ToLower()) {

    'up' {
        Assert-Env
        Assert-PortsFree
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
        docker compose exec app php database/run_migrations.php @Rest
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
