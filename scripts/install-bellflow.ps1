param(
    [switch]$SkipMigrate,
    [switch]$SkipBuild,
    [switch]$WithQueue
)

$ErrorActionPreference = 'Stop'

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

function Require-Command {
    param([string]$Name)

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Missing required command: $Name"
    }
}

Require-Command php
Require-Command composer
Require-Command npm

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "Created .env from .env.example"
    Write-Host "Update your .env values for database, app URL, mail, branding, and optional AI before going live."
}

Write-Host "Installing Composer dependencies..."
composer install

Write-Host "Installing npm dependencies..."
npm install

$envContent = Get-Content .env -Raw
if ($envContent -notmatch '^APP_KEY=base64:' -and $envContent -notmatch '(?m)^APP_KEY=base64:') {
    Write-Host "Generating APP_KEY..."
    php artisan key:generate --force
}

Write-Host "Clearing Laravel caches..."
php artisan optimize:clear

if (-not $SkipMigrate) {
    Write-Host "Running database migrations..."
    php artisan migrate --force
} else {
    Write-Host "Skipping migrations"
}

if (-not $SkipBuild) {
    Write-Host "Building frontend assets..."
    npm run build
} else {
    Write-Host "Skipping frontend build"
}

Write-Host "Running Bellflow health check..."
php artisan bellflow:contracts:check

if ($WithQueue) {
    Write-Host "Starting queue worker..."
    php artisan queue:work --queue=default
}

Write-Host "Bellflow install complete."
