# Bellflow Post-Install Verification

Use this checklist after deploying Bellflow.

## Core App

- `php artisan about` runs successfully
- `php artisan route:list --path=contracts` shows Contracts routes
- `php artisan route:list --path=client/contracts` shows portal contract routes
- `php artisan bellflow:contracts:check` reports `OK` for critical checks

## Branding

- Login page shows Bellflow name
- Portal uses Bellflow footer text
- Logo loads from `/images/bellflow-logo.jpg`
- Browser title uses Bellflow naming

## Contracts

- Contracts list loads
- Contract detail page loads
- Timeline renders
- Comment form works
- Payment schedule form works
- Public signing route resolves

## Payments

- At least one company gateway is configured
- Contract portal page shows available payment methods
- Payment schedule can generate a linked invoice
- Linked invoice opens from the contract portal or admin
- A manual/offline payment can still be recorded normally

## AI

- Queue worker is running
- `AI_ENABLED=true`
- `CONTRACTS_AI_ENABLED=true`
- `AI_BASE_URL` points to your Qwen server
- AI task can be queued from the contract admin page
- AI task completes and stores output

## Production Safety

- Real mail transport works
- Queue worker auto-starts on deploy
- Database backups are configured
- Redis is running if queues use Redis
- APP_KEY is backed up securely
