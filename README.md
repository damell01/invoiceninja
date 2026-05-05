<p align="center">
<a href ="https://www.youtube.com/watch?v=CxGxXiotv0I" target="_blank" title="Invoice Ninja Overview Video"><img src="https://raw.githubusercontent.com/hillelcoren/invoice-ninja/master/public/images/round_logo.png" alt="Sublime's custom image"/></a>
</p>

![v5-develop phpunit](https://github.com/invoiceninja/invoiceninja/workflows/phpunit/badge.svg?branch=v5-develop)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/d16c78aad8574466bf83232b513ef4fb)](https://www.codacy.com/gh/turbo124/invoiceninja/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=turbo124/invoiceninja&amp;utm_campaign=Badge_Grade)
<a href="https://cla-assistant.io/invoiceninja/invoiceninja"><img src="https://cla-assistant.io/readme/badge/invoiceninja/invoiceninja" alt="CLA assistant" /></a>

# Invoice Ninja 5

Invoice Ninja Version 5 is here! We've taken the best parts of version 4 and added the most requested features to create an invoicing application like no other. Check the [Invoice Ninja YouTube Channel](https://www.youtube.com/@appinvoiceninja) to get up to speed, or try the [Demo](https://react.invoicing.co/demo) now.

**Choose your setup**

- [Hosted](https://www.invoiceninja.com): Our hosted version is a Software as a Service (SaaS) solution. You're up and running in under 5 minutes, with no need to worry about hosting or server infrastructure.
- [Self-Hosted](https://www.invoiceninja.org): For those who prefer to manage their own hosting and server infrastructure. This version gives you full control and flexibility.

All Pro and Enterprise features from the hosted app are included in the source-available code. We offer a $40 per year white-label license to remove the Invoice Ninja branding from client-facing parts of the app.

#### Get social with us

* [Support Forum](https://forum.invoiceninja.com)
* [Slack](http://slack.invoiceninja.com)
* [Discord](https://discord.gg/ZwEdtfCwXA)
* [Instagram](https://www.instagram.com/appinvoiceninja)

#### Documentation

* [Invoice Ninja - API](https://api-docs.invoicing.co/)
* [Invoice Ninja - Developer Guide](https://invoiceninja.github.io/en/developer-guide/)
* [Invoice Ninja - User Guide](https://invoiceninja.github.io/en/user-guide/)
* [Invoice Ninja - Self-Hosted Installation Guide](https://invoiceninja.github.io/en/self-host-installation/)

## Installation Options and Clients

### Mobile Apps
* [iPhone](https://apps.apple.com/app/id1503970375?platform=iphone)
* [Android](https://play.google.com/store/apps/details?id=com.invoiceninja.app)
* [F-Droid](https://f-droid.org/en/packages/com.invoiceninja.app)

### Desktop Apps
* [macOS](https://apps.apple.com/app/id1503970375?platform=mac)
* [Windows](https://microsoft.com/en-us/p/invoice-ninja/9n3f2bbcfdr6)
* [Linux - Snap](https://snapcraft.io/invoiceninja)
* [Linux - Flatpak](https://flathub.org/apps/com.invoiceninja.InvoiceNinja)

### Self-Hosted Server Installation 
**Note:** The self-hosted options do support the desktop and mobile apps.

* [Server or VM](https://invoiceninja.github.io/en/self-host-installation/)
* [Docker File](https://hub.docker.com/r/invoiceninja/invoiceninja/)
* [Cloudron](https://www.cloudron.io/store/com.invoiceninja.cloudronapp2.html)
* [Softaculous](https://www.softaculous.com/apps/ecommerce/Invoice_Ninja)
* [Elestio](https://elest.io/open-source/invoiceninja)
* [YunoHost](https://apps.yunohost.org/app/invoiceninja5)

## Bellflow Deployment

This repository also includes a Bellflow-branded deployment path with:

- Bellflow white-label defaults
- the Contracts module
- portal contract routes
- payment schedule to invoice wiring
- gateway-aware payment method discovery using Invoice Ninja's existing payment stack
- optional async AI tasks using a self-hosted Qwen-compatible endpoint

If you are deploying Bellflow instead of stock Invoice Ninja, start here and then use the detailed docs:

- [Bellflow setup guide](docs/bellflow-setup.md)
- [Bellflow verification checklist](docs/bellflow-verification-checklist.md)
- [Bellflow production deploy checklist](docs/bellflow-deploy-checklist.md)
- [Private repo deploy options](docs/private-repo-deploy.md)

Useful env templates:

- [`.env.production.example`](.env.production.example) for host / VPS production installs
- [`.env.docker.example`](.env.docker.example) for Docker installs

### Bellflow VPS / CLI Install

Fastest path for a host install:

```sh
cp .env.production.example .env
./scripts/install-bellflow.sh
```

If you want a one-command Bellflow bootstrap instead of running each step manually, use:

```sh
./scripts/install-bellflow.sh
```

On Windows PowerShell:

```powershell
.\scripts\install-bellflow.ps1
```

Available script flags:

- `--skip-migrate`
- `--skip-build`
- `--with-queue`

1. Clone the repository and enter the project directory.

```sh
git clone <your-bellflow-repo-url>
cd invoiceninja
```

2. Install PHP dependencies.

```sh
composer install
```

3. Install frontend dependencies.

```sh
npm install
```

4. Create your environment file.

```sh
cp .env.example .env
```

5. Update `.env` with your real values:

- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- mail credentials
- Bellflow branding values
- optional AI values

6. Run migrations and clear caches.

```sh
php artisan migrate --force
php artisan optimize:clear
```

7. Build frontend assets.

```sh
npm run build
```

8. Run the Bellflow health check.

```sh
php artisan bellflow:contracts:check
```

9. If you want AI tasks or other async jobs, run a queue worker.

```sh
php artisan queue:work --queue=default
```

### Bellflow Docker Install

Bellflow can also be deployed with Docker.

Fastest path for Docker:

```sh
cp .env.docker.example .env
./scripts/install-bellflow-docker.sh
```

If you want a one-command Docker bootstrap, use:

```sh
./scripts/install-bellflow-docker.sh
```

On Windows PowerShell:

```powershell
.\scripts\install-bellflow-docker.ps1
```

Available script flags:

- `--skip-migrate`
- `--skip-health`
- `--logs`

Included example files:

- [deployment/docker/Bellflow.Dockerfile](deployment/docker/Bellflow.Dockerfile)
- [deployment/docker/docker-compose.bellflow.example.yml](deployment/docker/docker-compose.bellflow.example.yml)

Recommended flow:

1. Copy `.env.docker.example` to `.env`
2. Set your real app, database, mail, Redis, branding, and optional AI values
3. Build the image
4. Start the app, queue, database, and Redis services
5. Run migrations inside the app container
6. Run the Bellflow health check

Example commands:

```sh
docker compose -f deployment/docker/docker-compose.bellflow.example.yml build
docker compose -f deployment/docker/docker-compose.bellflow.example.yml up -d
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan migrate --force
docker compose -f deployment/docker/docker-compose.bellflow.example.yml exec app php artisan bellflow:contracts:check
```

For future updates, use:

```sh
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

### Bellflow Notes

- You do not need an SSH key just to build or run Docker locally.
- You only need SSH if you are connecting to a remote VPS shell, cloning from a private Git repo over SSH, or deploying through a remote Git-based workflow.
- If your code is already on the VPS, Docker can build it directly from the local folder there with no SSH key inside Docker itself.
- If your repo is private and you want the easiest first deploy, copy the repo to the VPS and run the install script there. If you want easier updates later, use an SSH deploy key. See [docs/private-repo-deploy.md](docs/private-repo-deploy.md).
- Payment gateways are configured in the app UI at `/#/settings/company_gateways`.
- Contracts admin now includes a gateway readiness card and an AI connection test button.
- AI is intended to run only on background jobs and is now separated onto its own `ai` queue by default so slow model calls do not block normal app work.
- The built-in web UI at `/setup` helps configure the application once the app is already bootable, but it does not install system packages, Composer dependencies, npm packages, or Docker services for you.
- Contracts reuse Invoice Ninja's normal payment gateway system, so this stays compatible with Stripe and other supported gateways instead of creating a second payment stack.
- Offline payments like check, cash, wire, or external POS can still be tracked as normal Invoice Ninja payments.
- AI is disabled until you set `AI_ENABLED=true` and provide a working Qwen-compatible endpoint.
- The current logo asset in `public/images/bellflow-logo.jpg` still visually says `DBellCreations`, so replace it when you have final Bellflow brand assets.

### Recommended Providers
* [Stripe](https://stripe.com/)
* [Postmark](https://postmarkapp.com/)

### SDKs available for easier API consumption
* [Go SDK](https://github.com/AshkanYarmoradi/go-invoice-ninja)

## [Advanced] Quick Hosting Setup

In addition to the official [Invoice Ninja - Self-Hosted Installation Guide](https://invoiceninja.github.io/en/self-host-installation/) we have a few commands for you.

```sh
git clone --depth 1 -b v5.11.53 https://github.com/invoiceninja/invoiceninja.git
cp .env.example .env
composer i -o --no-dev
```

**Note** replace v5.11.53 with the latest tag version, you will also want to ensure that when performing updates, you use the latest tag version rather than a particular branch, ie v5-develop. This will ensure that you are not pulling in work in progress code.

Please Note: 
Your APP_KEY in the .env file is used to encrypt data, if you lose this you will not be able to run the application.

Run if you want to load sample data, remember to configure .env
```sh
php artisan migrate:fresh --seed && php artisan db:seed && php artisan ninja:create-test-data
```

To run the web server
```sh
php artisan serve 
```

Navigate to (replace localhost with the appropriate domain)
```
http://localhost:8000/setup - To setup your configuration if you did not load sample data.
http://localhost:8000/ - For Administrator Logon

user: small@example.com
pass: password

http://localhost:8000/client/login - For Client Portal

user: user@example.com
pass: password
```
## Developers Guide

In addition to the official [Invoice Ninja - Developer Guide](https://invoiceninja.github.io/en/developer-guide/) we've got your back with some insights.

### App Design

The API and client portal have been developed using [Laravel](https://laravel.com) if you wish to contribute to this project familiarity with Laravel is essential.

When inspecting functionality of the API, the best place to start would be in the routes/api.php file which describes all of the availabe API endpoints. The controller methods then describe all the entry points into each domain of the application, ie InvoiceController / QuoteController

The average API request follows this path into the application.

* Middleware processes the request initially inspecting the domain being requested + provides the authentication layer.
* The request then passes into a Form Request (Type hinted in the controller methods) which is used to provide authorization and also validation of the request. If successful, the request is then passed into the controller method where it is digested, here is an example:

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

Here for example we are storing a new invoice, we pass the validated request along with a factory into the invoice repository where it is processed and saved.

The returned invoice then passes through its service class (app/Services/Invoice) where various actions are performed.

A event is then fired which notifies listeners in the application (app/Providers/EventServiceProvider) which perform non blocking sub tasks 

Finally the invoice is transformed (app/Transformers/) and returned as a response via Fractal.

### Developer environment

Using the Quick Hosting Setup describe above you can quickly get started building out your development environment. Instead of using 

```
composer i -o --no-dev
``` 

use

```
composer i -o
```

This provides the developer tools including phpunit which allows the test suite to be run.

If you are considering contributing back to the main repository, please add in any tests for new functionality / modifications. This will greatly increase the chances of your PR being accepted

Also, if you plan any additions for the main repository, you may want to discuss this with us first on Slack where we can assist with any technical information and provide advice.

## Credits
* [Hillel Coren](https://hillelcoren.com/)
* [David Bomba](https://github.com/turbo124)
* [Benjamin Beganović](https://github.com/beganovich)
* [All Contributors](https://github.com/invoiceninja/invoiceninja/graphs/contributors)


## Want More?
Checkout our other projects here!

[Event Schedule](https://www.eventschedule.com/)


## Security

If you find a security issue with this application, please send an email to contact@invoiceninja.com.
Please follow responsible disclosure procedures if you detect an issue.
For further information on responsible disclosure please read [here](https://cheatsheetseries.owasp.org/cheatsheets/Vulnerability_Disclosure_Cheat_Sheet.html).

## License
Invoice Ninja is released under the Elastic License.  
See [LICENSE](LICENSE) for details.
