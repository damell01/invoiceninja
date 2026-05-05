#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

SKIP_MIGRATE=0
SKIP_BUILD=0
RUN_QUEUE=0

for arg in "$@"; do
  case "$arg" in
    --skip-migrate)
      SKIP_MIGRATE=1
      ;;
    --skip-build)
      SKIP_BUILD=1
      ;;
    --with-queue)
      RUN_QUEUE=1
      ;;
    *)
      echo "Unknown option: $arg"
      echo "Usage: ./scripts/install-bellflow.sh [--skip-migrate] [--skip-build] [--with-queue]"
      exit 1
      ;;
  esac
done

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1"
    exit 1
  fi
}

require_cmd php
require_cmd composer
require_cmd npm

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
  echo "Update your .env values for database, app URL, mail, branding, and optional AI before going live."
fi

echo "Installing Composer dependencies..."
composer install

echo "Installing npm dependencies..."
npm install

if ! grep -q '^APP_KEY=base64:' .env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Clearing Laravel caches..."
php artisan optimize:clear

if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
  echo "Running database migrations..."
  php artisan migrate --force
else
  echo "Skipping migrations"
fi

if [[ "$SKIP_BUILD" -eq 0 ]]; then
  echo "Building frontend assets..."
  npm run build
else
  echo "Skipping frontend build"
fi

echo "Running Bellflow health check..."
php artisan bellflow:contracts:check

if [[ "$RUN_QUEUE" -eq 1 ]]; then
  echo "Starting queue worker..."
  php artisan queue:work --queue=default
fi

echo "Bellflow install complete."
