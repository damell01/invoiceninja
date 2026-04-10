# Hostinger Shared Hosting Deployment (DBell Billing)

This guide assumes Hostinger shared hosting where the public web root is `/public_html/` and the Laravel app must live in a subfolder.

## Recommended Folder Structure

```
/public_html/                (web root)
  index.php                  (from deployment/hostinger/public_html/index.php)
  .htaccess                  (from deployment/hostinger/public_html/.htaccess)
  /dbellbilling/             (full Laravel app here)
    app/
    bootstrap/
    config/
    public/
    resources/
    routes/
    storage/
    vendor/
    .env                      (created by the installer)
```

If you want a different subfolder name, update both the root `index.php` and `.htaccess` accordingly.

## Upload Steps

1. Upload the entire repository to `/public_html/dbellbilling/`.
2. Upload `deployment/hostinger/public_html/index.php` to `/public_html/index.php`.
3. Upload `deployment/hostinger/public_html/.htaccess` to `/public_html/.htaccess`.
4. Make these folders writable:
   - `/public_html/dbellbilling/storage`
   - `/public_html/dbellbilling/bootstrap/cache`
   - `/public_html/dbellbilling/public`
   - `/public_html/dbellbilling/.env` (the installer will create it)
5. Visit `https://your-domain.com/setup` to run the web installer.

The browser installer writes the `.env` file, generates the app key, runs migrations/seeders, and creates the first account.

## Root index.php (Example)

```
<?php
define('LARAVEL_START', microtime(true));

$appFolder = __DIR__ . '/dbellbilling';

if (! is_file($appFolder . '/bootstrap/app.php')) {
    http_response_code(500);
    echo 'DBell Billing bootstrap path not found. Expected app folder at /public_html/dbellbilling';
    exit;
}

require $appFolder . '/vendor/autoload.php';
$app = require_once $appFolder . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
```

## .htaccess (Example)

```
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule "^\.env" - [F,L]
    RewriteRule "^dbellbilling/(.*\.env.*)$" - [F,L]
    RewriteRule "^dbellbilling/storage/(.*)$" - [F,L]

    RewriteCond %{REQUEST_URI} !^/dbellbilling/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ dbellbilling/public/$1 [L]
</IfModule>
```

## Required PHP Version and Extensions

From `composer.json`:

- PHP >= 8.2
- Extensions: `bcmath`, `curl`, `dom`, `json`, `libxml`, `simplexml`

## Minimum .env Values

The installer writes `.env`. If you want to pre-fill it, set at least:

```
APP_NAME="DBell Billing"
APP_URL="https://your-domain.com"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

BRAND_COMPANY_NAME="DBell Creations"
BRAND_SUPPORT_EMAIL="support@yourdomain.com"
BRAND_SUPPORT_NAME="DBell Creations Support"
BRAND_WEBSITE_URL="https://your-domain.com"
BRAND_SUPPORT_URL="https://your-domain.com/support"
BRAND_TERMS_URL="https://your-domain.com/terms"
BRAND_PRIVACY_URL="https://your-domain.com/privacy"
BRAND_BROWSER_TITLE="DBell Billing"
BRAND_FOOTER_LINK_TEXT="DBell Creations"
BRAND_FOOTER_TEXT="Powered by"

ENABLE_SERVICE_WORKER=false
```

## Common Shared-Hosting Pitfalls

- Queues/cron: Hostinger shared hosting may not run Laravel queues by default. Use Hostinger cron jobs or disable queue workers if not needed.
- PDF generation: Some PDF generators require binaries not available in shared hosting. Use the built-in HTML/PDF options or a hosted PDF option.
- Symlinks: If `storage:link` can’t run, manually create a `public/storage` folder or ask Hostinger support to allow symlinks.

## Optional Artisan Commands (if you have CLI)

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## Files in This Deployment Package

```
deployment/hostinger/public_html/index.php
deployment/hostinger/public_html/.htaccess
deployment/hostinger/DEPLOYMENT.md
```
