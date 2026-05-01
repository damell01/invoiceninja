# Contracts Module — Setup Guide

This guide explains how to activate the Contracts module in your Invoice Ninja v5 instance.

## 1. Register the Service Provider

Add the provider to your `bootstrap/providers.php` (Laravel 11) or `config/app.php` (Laravel 10).

**Laravel 11 — `bootstrap/providers.php`:**
```php
return [
    // ... existing providers ...
    App\Providers\ContractServiceProvider::class,
];
```

**Laravel 10 — `config/app.php` (inside the `providers` array):**
```php
App\Providers\ContractServiceProvider::class,
```

## 2. Run Migrations

```bash
php artisan migrate
```

This creates the following tables:
- `contracts`
- `contract_templates`
- `contract_signers`
- `contract_audit_logs`
- `contract_clauses`

## 3. Publish Storage Link

Contract PDFs are stored on the `local` disk under `storage/app/contracts/`. Make sure storage is linked if you need web access:

```bash
php artisan storage:link
```

## 4. Queue Worker (Recommended)

Email notifications and reminder jobs use Laravel's queue. Start a worker:

```bash
php artisan queue:work --sleep=3 --tries=3
```

Or use Supervisor to keep it running in production.

## 5. Scheduler (Required for Auto-Expiry)

Add the Laravel scheduler to your crontab:

```
* * * * * cd /path/to/your/invoiceninja && php artisan schedule:run >> /dev/null 2>&1
```

This runs `contracts:expire` hourly to:
- Mark expired contracts
- Send 48-hour expiration warning emails

## 6. API Endpoints

All admin endpoints are prefixed with `/api/v1/` and require a valid API token.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/v1/contracts` | List / Create |
| GET/PUT/DELETE | `/api/v1/contracts/{id}` | Show / Update / Delete |
| POST | `/api/v1/contracts/{id}/send` | Send to signers |
| POST | `/api/v1/contracts/{id}/approve` | Approve |
| POST | `/api/v1/contracts/{id}/cancel` | Cancel |
| POST | `/api/v1/contracts/{id}/counter-sign` | Counter-sign |
| POST | `/api/v1/contracts/{id}/new-version` | Create new version |
| POST | `/api/v1/contracts/{id}/signers` | Add signer |
| DELETE | `/api/v1/contracts/{id}/signers/{signer}` | Remove signer |
| GET | `/api/v1/contracts/{id}/pdf` | Generate/download PDF |
| GET | `/api/v1/contracts/{id}/signed-pdf` | Download signed PDF |
| GET | `/api/v1/contracts/{id}/audit-log` | Fetch audit log |
| GET | `/api/v1/contracts/variables` | List merge variables |
| POST | `/api/v1/contracts/preview` | Preview rendered contract |
| POST | `/api/v1/contracts/from-quote/{quoteId}` | Create from quote |
| POST | `/api/v1/contracts/from-invoice/{invoiceId}` | Create from invoice |
| POST | `/api/v1/contracts/bulk` | Bulk actions |
| GET/POST | `/api/v1/contract-templates` | Templates list / create |
| GET/PUT/DELETE | `/api/v1/contract-templates/{id}` | Template CRUD |
| POST | `/api/v1/contract-templates/{id}/clone` | Clone template |
| POST | `/api/v1/contract-templates/{id}/create-contract` | Create contract from template |
| GET | `/api/v1/contract-templates/categories` | List categories |
| GET/POST | `/api/v1/contract-clauses` | Clauses list / create |
| GET/PUT/DELETE | `/api/v1/contract-clauses/{id}` | Clause CRUD |
| GET | `/api/v1/contract-clauses/categories` | List clause categories |

## 7. Client Portal

Clients can access contracts at:

- **List:** `https://yourdomain.com/client/contracts`
- **View / Sign:** `https://yourdomain.com/client/contracts/{token}`
- **Download:** `https://yourdomain.com/client/contracts/{token}/download`

## 8. Public Signing Links

Public (unauthenticated) signing links:

- **Sign:** `https://yourdomain.com/contract/sign/{token}` — main signing page
- **Signer-specific:** `https://yourdomain.com/contract/s/{signerToken}` — auto-loads signer context
- **Signed confirmation:** `https://yourdomain.com/contract/signed/{token}`

These are throttled to 20 requests/minute.

## 9. Contract Templates

The system ships with 7 template categories:

1. `website_project` — Website Project Agreement
2. `monthly_maintenance` — Monthly Maintenance Contract
3. `saas_subscription` — SaaS Subscription Agreement
4. `consulting_agreement` — Consulting Agreement
5. `service_agreement` — Service Agreement
6. `retainer_agreement` — Retainer Agreement
7. `proposal_to_contract` — Proposal to Contract

Create templates via `POST /api/v1/contract-templates` or through the admin UI.

## 10. Merge Variables

All contract bodies support merge variables. Example:

```
This agreement is between {{company.name}} and {{client.name}}.
Payment of {{invoice.total}} is due within {{payment_terms}}.
```

Full list of variables available at: `GET /api/v1/contracts/variables`

## 11. Contract Clauses Library

12 built-in clause categories:

- payment_terms, late_fees, cancellation, refunds, scope_of_work
- ip_ownership, hosting_terms, maintenance, confidentiality, termination
- change_requests, acceptance_criteria

Manage at: `GET /api/v1/contract-clauses`

## 12. Payment Gateway Integration

The Contracts module ties into Invoice Ninja's existing payment infrastructure:

- **No separate gateway setup required.** All payment gateways already configured in your Invoice Ninja admin panel (Settings → Online Payments) are available.
- Convert a signed contract to an invoice via: `POST /api/v1/contracts/from-invoice/{invoiceId}` or link an existing invoice when creating the contract.
- Clients pay through the standard Invoice Ninja payment flow after the contract is linked to an invoice.
- **Recommended flow:**
  1. Create quote → Convert to contract (`from-quote`)
  2. Client signs contract
  3. Convert to invoice (`/api/v1/invoices` or link invoice_id on contract)
  4. Client pays via existing gateway (Stripe, PayPal, etc.)

## 13. Status Flow

```
draft → internal_review → approved → sent → viewed → signed
                                          ↓              ↓
                                       declined        expired
                                          ↓
                                       cancelled
```

## 14. Permissions

The module respects Invoice Ninja's existing permission system. Users need:

- `view_contract` — View contracts
- `create_contract` — Create contracts
- `edit_contract` — Edit/update contracts
- `delete_contract` — Delete contracts
- `send_contract` — Send contracts to signers

Admins and company owners bypass permission checks.

## 15. Audit Trail & Legal Safety

- All contract_audit_logs records are **append-only** (updates are blocked at the model level)
- Signed contracts are **locked** (`is_locked = true`) — body cannot be edited
- A SHA-256 hash of the contract body is stored at signing time
- IP address and user agent are recorded for every signing action
- New versions copy the original and increment the version counter

## 16. Troubleshooting

**PDFs not generating?**
- Ensure `barryvdh/laravel-dompdf` is installed: `composer require barryvdh/laravel-dompdf`
- Check `storage/app/contracts/` is writable

**Emails not sending?**
- Check your `.env` mail settings (MAIL_MAILER, MAIL_HOST, etc.)
- Ensure queue worker is running for queued mail jobs

**Routes not found?**
- Confirm `ContractServiceProvider` is registered
- Run `php artisan route:clear && php artisan route:cache`

**Permission denied errors?**
- Ensure the authenticated user belongs to the same `company_id` as the contract
- Check `ContractPolicy` is being registered (it is, in the service provider)
