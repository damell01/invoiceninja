# Bellflow Setup Guide

This guide is for your Bellflow-branded Invoice Ninja install with the Contracts module, portal integration, payment schedule wiring, and optional self-hosted Qwen AI support.

If you want the shortest version:

- host / CLI install: `cp .env.production.example .env && ./scripts/install-bellflow.sh`
- Docker install: `cp .env.docker.example .env && ./scripts/install-bellflow-docker.sh`

Recommended env templates:

- [`.env.production.example`](/abs/path/c:/Users/Shild/Downloads/invoiceninja/.env.production.example) for a production VPS or VM
- [`.env.docker.example`](/abs/path/c:/Users/Shild/Downloads/invoiceninja/.env.docker.example) for Docker

Bellflow also supports UI visibility controls:

- `CONTRACTS_UI_ENABLED`
- `CONTRACTS_ADMIN_ENABLED`
- `CONTRACTS_PORTAL_ENABLED`
- `CONTRACTS_PORTAL_MENU_ENABLED`
- `CONTRACTS_PUBLIC_SIGN_ENABLED`
- `BELLFLOW_MODULE_VISIBILITY_MODE`
- `BELLFLOW_ALLOWED_MODULES`
- `BELLFLOW_HIDDEN_MODULES`
- `BELLFLOW_HIDDEN_PORTAL_ITEMS`

After install, the remaining business setup is mostly done in the app UI:

- payment gateways: `/#/settings/company_gateways`
- company branding and settings: the normal Bellflow / Invoice Ninja settings UI
- AI connection verification: the Contracts admin page now includes a Qwen connection test button

## What This Setup Includes

- Bellflow branding defaults
- Contracts module under `Modules/Contracts`
- Portal contract pages
- Payment schedules that generate normal Invoice Ninja invoices
- Gateway-aware payment method discovery using Invoice Ninja's existing payment stack
- Optional async AI tasks against a self-hosted Qwen-compatible endpoint

## Prerequisites

- PHP and Composer if running directly on the host
- Node.js and npm for frontend builds
- MySQL or MariaDB
- Redis recommended if you want queued jobs
- A real `.env` file
- A running queue worker if you enable AI tasks

## Quick Local Setup

If you want a guided CLI bootstrap, use one of these scripts first:

```bash
./scripts/install-bellflow.sh
```

On Windows PowerShell:

```powershell
.\scripts\install-bellflow.ps1
```

These scripts:

- install Composer dependencies
- install npm dependencies
- create `.env` if missing
- generate `APP_KEY` if needed
- clear caches
- run migrations unless you skip them
- build frontend assets unless you skip them
- run the Bellflow health check

Optional flags:

- `--skip-migrate`
- `--skip-build`
- `--with-queue`

1. Install backend dependencies.

```bash
composer install
```

2. Install frontend dependencies.

```bash
npm install
```

3. Create your environment file.

```bash
cp .env.example .env
```

4. Update these required values in `.env`:

- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_MAILER` and your mail credentials

5. Set Bellflow branding.

Recommended values:

```dotenv
APP_NAME="Bellflow"
BRAND_COMPANY_NAME="Bellflow"
BRAND_SUPPORT_EMAIL="support@bellflow.app"
BRAND_SUPPORT_NAME="Bellflow"
BRAND_WEBSITE_URL="https://your-domain.example"
BRAND_SUPPORT_URL="https://your-domain.example/support"
BRAND_TERMS_URL="https://your-domain.example/terms"
BRAND_PRIVACY_URL="https://your-domain.example/privacy"
BRAND_BROWSER_TITLE="Bellflow"
BRAND_FOOTER_LINK_TEXT="Bellflow"
BRAND_FOOTER_TEXT="Powered by Bellflow"
BRAND_LOGO_LIGHT="images/bellflow-logo.jpg"
BRAND_LOGO_DARK="images/bellflow-logo.jpg"
BRAND_APP_LOGO="https://your-domain.example/images/bellflow-logo.jpg"
BRAND_FAVICON="images/bellflow-logo.jpg"
```

6. Configure Contracts and AI if needed.

```dotenv
QUEUE_CONNECTION=database

CONTRACTS_PAYMENT_PROCESSOR=invoice_ninja_gateways
CONTRACTS_CHECKOUT_SURFACE=invoice_ninja_portal
CONTRACTS_GENERATE_INVOICE_ON_SIGN=true

AI_ENABLED=true
AI_PROVIDER=qwen
AI_BASE_URL=http://your-qwen-host:8000
AI_CHAT_ENDPOINT=/v1/chat/completions
AI_API_KEY=
AI_MODEL=qwen
AI_TIMEOUT=120
AI_CONNECT_TIMEOUT=10
AI_QUEUE=default

CONTRACTS_AI_ENABLED=true
CONTRACTS_AI_QUEUE=default
CONTRACTS_AI_DEFAULT_ACTION=summarize
```

7. Run database migrations.

```bash
php artisan migrate --force
```

8. Clear caches and rebuild config.

```bash
php artisan optimize:clear
```

9. Build frontend assets.

```bash
npm run build
```

10. Start a queue worker if AI tasks or future async actions are enabled.

```bash
php artisan queue:work --queue=default
```

## Docker Setup

Yes, you can run this with Docker.

Fastest Docker path:

```bash
cp .env.docker.example .env
./scripts/install-bellflow-docker.sh
```

If you want a guided Docker bootstrap, use one of these scripts:

```bash
./scripts/install-bellflow-docker.sh
```

On Windows PowerShell:

```powershell
.\scripts\install-bellflow-docker.ps1
```

These scripts:

- create `.env` if missing
- build the Bellflow app image
- start app, queue, db, and redis services
- run migrations unless you skip them
- run the Bellflow health check unless you skip it
- optionally follow app and queue logs

Optional flags:

- `--skip-migrate`
- `--skip-health`
- `--logs`

For future updates, use:

```bash
./scripts/update-bellflow-docker.sh
```

On Windows PowerShell:

```powershell
.\scripts\update-bellflow-docker.ps1
```

Optional update flags:

- `--pull`
- `--skip-migrate`
- `--skip-health`
- `--logs`

This repository does not include an official Bellflow `docker-compose.yml`, so the safest approach is:

1. Start from the official Invoice Ninja container setup
2. Layer your Bellflow repo customizations into a custom app image
3. Keep MySQL and Redis as separate services
4. Run an app container and a queue worker container from the same image

Example files are included here:

- [deployment/docker/Bellflow.Dockerfile](/abs/path/c:/Users/Shild/Downloads/invoiceninja/deployment/docker/Bellflow.Dockerfile)
- [deployment/docker/docker-compose.bellflow.example.yml](/abs/path/c:/Users/Shild/Downloads/invoiceninja/deployment/docker/docker-compose.bellflow.example.yml)
- [docs/bellflow-deploy-checklist.md](/abs/path/c:/Users/Shild/Downloads/invoiceninja/docs/bellflow-deploy-checklist.md)
- [docs/private-repo-deploy.md](/abs/path/c:/Users/Shild/Downloads/invoiceninja/docs/private-repo-deploy.md)

Recommended Docker flow:

1. Copy `.env.docker.example` to `.env`
2. Update `.env` with your real domain, DB, Redis, mail, and AI values
3. Place your final logo files in `public/images/`
4. Build the app image
5. Start app, queue, db, and redis services
6. Run migrations inside the app container
7. Build frontend assets during the image build

The included Dockerfile uses multi-stage builds:

- Composer stage for PHP dependencies
- Node stage for Vite assets
- Official Invoice Ninja runtime image for the final app container

That keeps the runtime image cleaner and avoids assuming Composer or Node are present in the final container.

Example commands:

```bash
docker compose -f deployment/docker/docker-compose.bellflow.example.yml build
docker compose -f deployment/docker/docker-compose.bellflow.example.yml up -d
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan migrate --force
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan bellflow:contracts:check
```

## Do You Need SSH Keys?

Usually, no.

You do not need SSH keys for:

- building Docker locally on your own machine
- running `docker compose up`
- using the included Bellflow Docker scripts

You may need SSH keys when:

- logging into a VPS over SSH
- cloning a private repository with `git@...`
- using a CI/CD pipeline that pulls private code from Git over SSH

Simple rule:

- local machine + local repo + Docker = no SSH key needed for Docker itself
- remote VPS shell access = SSH usually needed to access the server
- private Git repo = SSH key or HTTPS token needed to pull code

## Qwen Compatibility Notes

The current AI client assumes an OpenAI-style chat endpoint:

- base URL from `AI_BASE_URL`
- path from `AI_CHAT_ENDPOINT`
- request body with `model` and `messages`

If your Qwen server uses a different schema, update:

- [config/ai.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/config/ai.php)
- [app/Services/AI/QwenClient.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/app/Services/AI/QwenClient.php)

## Payment Gateway Notes

Contracts do not implement a second payment system.

Instead:

- contract payment schedules generate standard Invoice Ninja invoices
- client-facing payment methods are resolved through the existing Invoice Ninja gateway stack
- supported methods depend on the gateways configured for that company and client
- offline payment tracking like check, cash, wire, and external POS can still be recorded by staff as normal Invoice Ninja payments

This means Stripe, PayPal, hosted gateways, ACH, custom gateways, and other supported methods can still participate without rewriting the Contracts module.

In practice, the setup flow is:

1. install Bellflow
2. log into the admin UI
3. open `/#/settings/company_gateways`
4. connect Stripe or another supported gateway
5. return to a contract and confirm the Gateway Setup card shows your configured gateways

## First Verification Run

After setup, run:

```bash
php artisan bellflow:contracts:check
php artisan route:list --path=contracts
php artisan route:list --path=client/contracts
php artisan test tests/Unit/Contracts/ContractPaymentMethodServiceTest.php tests/Unit/Contracts/ContractPaymentScheduleServiceTest.php tests/Unit/Contracts/QwenClientTest.php
```

## Recommended First Live Test

1. Create a client
2. Create a contract
3. Add a payment schedule
4. Generate a linked invoice
5. Open the client portal contract page
6. Confirm payment methods appear
7. Open the client portal sign flow and confirm the public signer route loads with the signature pad
8. Pay the linked invoice using an enabled gateway
9. Queue one AI task and confirm the worker completes it

## Current Known Gaps

- Full end-to-end runtime validation still depends on your real database, queue worker, mail setup, and gateway credentials
- AI task results are stored and shown, but they are not yet applied directly into the editor with an approval workflow
