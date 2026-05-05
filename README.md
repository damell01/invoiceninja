<p align="center">
<img src="public/images/bellflow-logo.svg" alt="Bellflow logo" width="360">
</p>

# Bellflow

Bellflow is a white-labeled, self-hosted business platform built on top of Invoice Ninja with deeper Contracts workflows, Bellflow branding, payment schedule automation, customer portal integration, and optional self-hosted AI assistance.

This repo is focused on Bellflow as the product you deploy and run. Invoice Ninja remains the upstream application foundation, but the install flow, branding, Contracts tooling, and deployment docs here are tailored for Bellflow.

## Bellflow Overview

Bellflow in this repo includes:

- Bellflow branding defaults
- a native-feeling Contracts module
- portal contract pages and public signing flow
- payment schedule to invoice wiring
- gateway-aware payment method discovery using the existing Invoice Ninja payment stack
- optional async AI tasks against a self-hosted Qwen-compatible endpoint
- Docker and CLI bootstrap scripts for easier installs and updates

## Bellflow Deployment

If you are deploying Bellflow, start here:

- [Bellflow setup guide](docs/bellflow-setup.md)
- [Bellflow verification checklist](docs/bellflow-verification-checklist.md)
- [Bellflow production deploy checklist](docs/bellflow-deploy-checklist.md)
- [Private repo deploy options](docs/private-repo-deploy.md)

Useful env templates:

- [`.env.production.example`](.env.production.example) for host / VPS production installs
- [`.env.docker.example`](.env.docker.example) for Docker installs

## Bellflow VPS / CLI Install

Fastest path for a host install:

```sh
cp .env.production.example .env
./scripts/install-bellflow.sh
```

Windows PowerShell:

```powershell
.\scripts\install-bellflow.ps1
```

Available script flags:

- `--skip-migrate`
- `--skip-build`
- `--with-queue`

Manual host flow:

```sh
git clone <your-bellflow-repo-url>
cd <your-bellflow-project-folder>
cp .env.production.example .env
composer install
npm install
php artisan migrate --force
npm run build
php artisan bellflow:contracts:check
```

If AI is enabled in production, run separate queue workers:

```sh
php artisan queue:work --queue=default
php artisan queue:work --queue=ai --tries=1 --timeout=180
```

## Bellflow Docker Install

Fastest path for Docker:

```sh
cp .env.docker.example .env
./scripts/install-bellflow-docker.sh
```

Windows PowerShell:

```powershell
.\scripts\install-bellflow-docker.ps1
```

Available Docker install flags:

- `--skip-migrate`
- `--skip-health`
- `--logs`

Included example files:

- [deployment/docker/Bellflow.Dockerfile](deployment/docker/Bellflow.Dockerfile)
- [deployment/docker/docker-compose.bellflow.example.yml](deployment/docker/docker-compose.bellflow.example.yml)

Example Docker commands:

```sh
docker compose -f deployment/docker/docker-compose.bellflow.example.yml build
docker compose -f deployment/docker/docker-compose.bellflow.example.yml up -d
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan migrate --force
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan bellflow:contracts:check
```

For future updates:

```sh
./scripts/update-bellflow-docker.sh
```

Or, if the VPS can pull your private repo directly:

```sh
./scripts/update-bellflow-docker.sh --pull
```

Windows PowerShell:

```powershell
.\scripts\update-bellflow-docker.ps1
```

Optional Docker update flags:

- `--pull`
- `--skip-migrate`
- `--skip-health`
- `--logs`

## Bellflow Notes

- You do not need an SSH key just to build or run Docker locally.
- You only need SSH if you are connecting to a remote VPS shell, cloning from a private Git repo over SSH, or deploying through a remote Git-based workflow.
- If your repo is private and you want the easiest first deploy, copy the repo to the VPS and run the install script there. If you want easier updates later, use an SSH deploy key. See [docs/private-repo-deploy.md](docs/private-repo-deploy.md).
- Payment gateways are configured in the app UI at `/#/settings/company_gateways`.
- Contracts admin includes a gateway readiness card and an AI connection test button.
- AI is intended to run only on background jobs and is separated onto its own `ai` queue by default so slow model calls do not block normal app work.
- The built-in web UI at `/setup` helps configure the application once the app is already bootable, but it does not install system packages, Composer dependencies, npm packages, or Docker services for you.
- Contracts reuse the underlying Invoice Ninja payment gateway system, so Bellflow stays compatible with Stripe and other supported gateways instead of creating a second payment stack.
- Offline payments like check, cash, wire, or external POS can still be tracked as normal Bellflow payments.
- AI is disabled until you set `AI_ENABLED=true` and provide a working Qwen-compatible endpoint.

## Recommended Providers

- [Stripe](https://stripe.com/)
- [Postmark](https://postmarkapp.com/)

## Advanced Host Setup

If you want a more manual Bellflow host setup instead of using the helper scripts, this is the shortest direct flow.

```sh
git clone <your-bellflow-repo-url>
cd <your-bellflow-project-folder>
cp .env.production.example .env
composer install --optimize-autoloader --no-dev
npm install
php artisan migrate --force
npm run build
php artisan bellflow:contracts:check
```

Important:
Your `APP_KEY` in `.env` is used to encrypt application data. Back it up securely. Losing it can make existing encrypted data unreadable.

If you want local sample data for development:

```sh
php artisan migrate:fresh --seed && php artisan db:seed && php artisan ninja:create-test-data
```

To run the local web server:

```sh
php artisan serve
```

Then open:

```text
http://localhost:8000/setup
http://localhost:8000/
http://localhost:8000/client/login
```

## Developer Guide

Bellflow is built on Laravel and on the Invoice Ninja application architecture. If you plan to extend Bellflow, it helps to understand both the upstream conventions and the Bellflow-specific module/layout changes in this repo.

### App Design

The API and client portal are Laravel-based. Contracts, branding, deployment helpers, and Bellflow-specific UX live alongside that foundation.

When inspecting backend functionality, a good starting point is:

- `routes/api.php`
- `routes/web.php`
- `routes/client.php`
- `Modules/Contracts/routes/*`

The typical request flow looks like this:

- Middleware handles request context and authentication.
- A Form Request performs authorization and validation.
- The controller hands off to repositories and service classes.
- Events and listeners take care of non-blocking follow-up work.
- Transformers shape the final API response where applicable.

Example:

```php
public function store(StoreInvoiceRequest $request)
{
    $invoice = $this->invoice_repo->save($request->all(), InvoiceFactory::create(auth()->user()->company()->id, auth()->user()->id));

    $invoice = $invoice->service()
                        ->fillDefaults()
                        ->triggeredActions($request)
                        ->adjustInventory()
                        ->save();

    event(new InvoiceWasCreated($invoice, $invoice->company, Ninja::eventVars(auth()->user() ? auth()->user()->id : null)));

    return $this->itemResponse($invoice);
}
```

In the base app, repositories and service classes handle most business operations. Bellflow follows the same general pattern, especially inside `Modules/Contracts`.

### Developer Environment

For a development environment, use the Bellflow install flow but avoid `--no-dev` Composer installs.

Instead of:

```sh
composer install --optimize-autoloader --no-dev
```

use:

```sh
composer install --optimize-autoloader
```

That keeps development tools available, including PHPUnit and other local testing dependencies.

If you extend Bellflow, add tests for behavior changes where practical, especially around Contracts, payments, AI, and deployment-sensitive flows.

If you plan to contribute changes back upstream to Invoice Ninja itself, review the upstream developer docs and contribution expectations separately.

## Upstream References

Bellflow is built on Invoice Ninja, so these upstream references are still useful when you need lower-level framework or platform context:

- [Invoice Ninja API](https://api-docs.invoicing.co/)
- [Invoice Ninja Developer Guide](https://invoiceninja.github.io/en/developer-guide/)
- [Invoice Ninja User Guide](https://invoiceninja.github.io/en/user-guide/)
- [Invoice Ninja Self-Hosted Installation Guide](https://invoiceninja.github.io/en/self-host-installation/)

## Credits

Bellflow is built on top of Invoice Ninja, and this repo benefits from the upstream maintainers and contributors.

- [Hillel Coren](https://hillelcoren.com/)
- [David Bomba](https://github.com/turbo124)
- [Benjamin Beganovic](https://github.com/beganovich)
- [All Contributors](https://github.com/invoiceninja/invoiceninja/graphs/contributors)

## Security

If you find a security issue in your Bellflow deployment, follow responsible disclosure procedures and route the report according to your own team or deployment process.

If the issue is clearly in upstream Invoice Ninja code, you should also consider notifying the upstream maintainers through their published security channel.

Please follow responsible disclosure procedures if you detect an issue.
For further information on responsible disclosure please read [here](https://cheatsheetseries.owasp.org/cheatsheets/Vulnerability_Disclosure_Cheat_Sheet.html).

## License

Bellflow in this repository remains based on Invoice Ninja and is therefore still governed by the upstream licensing terms where applicable.
See [LICENSE](LICENSE) for details.
