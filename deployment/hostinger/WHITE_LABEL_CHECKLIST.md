# White-Label Checklist (DBell Billing)

## Removed/Replaced Branding

- App/browser title, login/register/password pages, and portal headers now use the branding config.
- Footer branding updated to DBell Creations link and text.
- Email templates updated to DBell Creations and branded URLs/logos.
- Default logos replaced with DBell Billing assets in `public/images` and Flutter assets.
- Client portal error/empty states and unsubscribe views updated to branded logo/alt text.
- Nordigen/Yodlee screens updated to branded logo and title strings.
- In-app and email references to Invoice Ninja licensing/upsells replaced with neutral wording.

## Remaining References (Non-User-Facing or Functional)

- `config/logging.php` uses `invoiceninja` in log channel names. Not user-facing.
- `config/branding.php` still points to `images/invoiceninja-*` filenames, but these files are replaced with DBell logos.
- PWA manifest `id` updated to `com.dbell.billing` (non-visible).
- PayPal partner attribution IDs include `invoiceninja_*` for payment integration. Changing these may impact PayPal partnership attribution.
- PDF design HTML comments reference old docs links (not visible in output).
- Source comments referencing the upstream repo (not rendered in UI).

## Manual Review (If You Want 100% Cosmetic Purity)

- Replace doc links in PDF design comments with your own documentation site.
- Decide whether to keep or change PayPal partner attribution IDs.
- Update `config/branding.php` default logo filenames if you want non-InvoiceNinja filenames on disk.
