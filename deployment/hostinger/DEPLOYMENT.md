# Hostinger Shared Hosting Deployment

Upload the full app to `/public_html/dbellbilling`.

Upload these two files into `/public_html/`:

- `deployment/hostinger/public_html/index.php`
- `deployment/hostinger/public_html/.htaccess`

Then browse to:

- `https://your-domain.com/setup`

The browser installer will write `.env`, generate the app key, migrate the database, seed defaults, and create the first account.
