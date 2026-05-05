#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

COMPOSE_FILE="deployment/docker/docker-compose.bellflow.example.yml"
RUN_PULL=0
RUN_MIGRATE=1
RUN_HEALTH=1
FOLLOW_LOGS=0

for arg in "$@"; do
  case "$arg" in
    --pull)
      RUN_PULL=1
      ;;
    --skip-migrate)
      RUN_MIGRATE=0
      ;;
    --skip-health)
      RUN_HEALTH=0
      ;;
    --logs)
      FOLLOW_LOGS=1
      ;;
    *)
      echo "Unknown option: $arg"
      echo "Usage: ./scripts/update-bellflow-docker.sh [--pull] [--skip-migrate] [--skip-health] [--logs]"
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

require_cmd docker

if ! docker compose version >/dev/null 2>&1; then
  echo "Docker Compose v2 is required."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "Missing .env. Copy .env.docker.example to .env first."
  exit 1
fi

if [[ "$RUN_PULL" -eq 1 ]]; then
  require_cmd git
  echo "Pulling latest code..."
  git pull
fi

echo "Rebuilding Bellflow Docker image..."
docker compose -f "$COMPOSE_FILE" build

echo "Restarting Bellflow services..."
docker compose -f "$COMPOSE_FILE" up -d

if [[ "$RUN_MIGRATE" -eq 1 ]]; then
  echo "Running database migrations in app container..."
  docker compose -f "$COMPOSE_FILE" exec app php artisan migrate --force
else
  echo "Skipping migrations"
fi

if [[ "$RUN_HEALTH" -eq 1 ]]; then
  echo "Running Bellflow health check in app container..."
  docker compose -f "$COMPOSE_FILE" exec app php artisan bellflow:contracts:check
else
  echo "Skipping health check"
fi

if [[ "$FOLLOW_LOGS" -eq 1 ]]; then
  echo "Following app, queue, and ai-queue logs..."
  docker compose -f "$COMPOSE_FILE" logs -f app queue ai-queue
fi

echo "Bellflow Docker update complete."
