# Bellflow Production Deploy Checklist

Use this checklist before you consider a Bellflow deployment live.

## 1. Code And Install Method

- Repo is present on the VPS
- You chose one install path:
  - Docker: `./scripts/install-bellflow-docker.sh`
  - Host / CLI: `./scripts/install-bellflow.sh`
- If the repo is private, you chose one access path:
  - copied repo to VPS manually, or
  - configured an SSH deploy key for future `git pull`

## 2. Environment

- `.env` exists
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` matches your real public domain
- `APP_KEY` is set

## 3. Database

- Database service is running
- DB credentials in `.env` are correct
- `php artisan migrate --force` has completed successfully
- Backups are configured

## 4. Branding

- `APP_NAME` is set to `Bellflow`
- Bellflow branding env vars are set
- Final logo files are in `public/images/`
- Favicon is set
- Browser title shows Bellflow
- Footer text shows Bellflow

## 5. Domain And HTTPS

- Domain points to the server
- HTTPS / SSL is enabled
- `REQUIRE_HTTPS=true` if your production setup expects it
- Reverse proxy headers are configured correctly if using Nginx, Traefik, or another proxy

## 6. Queue And Background Jobs

- Queue connection is set correctly
- Queue worker is running
- Worker restarts automatically on reboot or deploy
- Redis is running if queues use Redis

## 7. Mail

- Mail transport is configured
- A real test email sends successfully
- Contract emails can be delivered

## 8. Payments

- At least one Invoice Ninja gateway is configured
- Client-facing payment methods appear on linked contract invoices
- Manual/offline payment recording works for check, cash, or wire if you need it
- One real payment test was completed end to end

## 9. Contracts

- Contracts routes load
- Contract admin page loads
- Timeline/comments load
- Portal contract pages load
- Public signing page loads
- Signed PDF generation works

## 10. AI

- `AI_ENABLED=true` only if your Qwen service is really available
- `AI_BASE_URL` points to the correct host
- `AI_MODEL` is set
- One AI task has been queued and completed successfully

## 11. Health Checks

- `php artisan bellflow:contracts:check` passes
- Contract unit tests pass if you are validating locally
- Docker containers are healthy if using Docker

## 12. Update Workflow

- You know how you will update:
  - Docker: `./scripts/update-bellflow-docker.sh`
  - Docker with git pull: `./scripts/update-bellflow-docker.sh --pull`
- You know how you will roll back if an update fails

## 13. Final Smoke Test

- Create a client
- Create a contract
- Add a payment schedule
- Generate a linked invoice
- Open the client portal
- Confirm payment options appear
- Sign a contract
- Confirm audit/timeline updates appear
- Confirm one AI task runs successfully
