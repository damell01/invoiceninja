param(
    [switch]$SkipMigrate,
    [switch]$SkipHealth,
    [switch]$Logs
)

$ErrorActionPreference = 'Stop'

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

$ComposeFile = 'deployment/docker/docker-compose.bellflow.example.yml'

function Require-Command {
    param([string]$Name)

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Missing required command: $Name"
    }
}

Require-Command docker

docker compose version | Out-Null

if (-not (Test-Path .env)) {
    Copy-Item .env.docker.example .env
    Write-Host "Created .env from .env.docker.example"
    Write-Host "Update your .env values for database, app URL, mail, branding, and optional AI before going live."
}

Write-Host "Building Bellflow Docker image..."
docker compose -f $ComposeFile build

Write-Host "Starting Bellflow services..."
docker compose -f $ComposeFile up -d

if (-not $SkipMigrate) {
    Write-Host "Running database migrations in app container..."
    docker compose -f $ComposeFile exec app php artisan migrate --force
} else {
    Write-Host "Skipping migrations"
}

if (-not $SkipHealth) {
    Write-Host "Running Bellflow health check in app container..."
    docker compose -f $ComposeFile exec app php artisan bellflow:contracts:check
} else {
    Write-Host "Skipping health check"
}

if ($Logs) {
    Write-Host "Following app and queue logs..."
    docker compose -f $ComposeFile logs -f app queue
}

Write-Host "Bellflow Docker install complete."
