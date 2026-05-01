# Invoice Ninja Contracts Module Blueprint

This blueprint is tailored to the current self-hosted Invoice Ninja v5 repository in this workspace.

It is based on these repo facts:

- Laravel modules are already enabled through `nwidart/laravel-modules` in [composer.json](/abs/path/c:/Users/Shild/Downloads/invoiceninja/composer.json) and [config/modules.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/config/modules.php).
- Portal pages are Blade/Livewire driven through [routes/client.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/routes/client.php) and [app/Http/ViewComposers/PortalComposer.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/app/Http/ViewComposers/PortalComposer.php).
- Merge-variable rendering already exists via [app/Utils/TemplateEngine.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/app/Utils/TemplateEngine.php) and `HtmlEngine`.
- PDF generation is centralized via [app/Jobs/Entity/CreateRawPdf.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/app/Jobs/Entity/CreateRawPdf.php).
- Signature capture already exists in the portal via `signature_pad` and [app/Jobs/Invoice/InjectSignature.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/app/Jobs/Invoice/InjectSignature.php).
- White-label config is already partially customized in [config/branding.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/config/branding.php).

## 1. Executive Recommendation

Build Contracts as a real Laravel module at `Modules/Contracts`, not as loose files under `app/`.

Use this split:

1. `Modules/Contracts` owns data model, services, routes, portal pages, public signing pages, APIs, PDF generation, mailables, policies, and admin-facing Blade/Livewire workspace.
2. Existing Invoice Ninja core services are reused for clients, contacts, quotes, invoices, projects, documents, email transport, PDF conversion, auth, permissions, and branding.
3. Main Invoice Ninja SPA integration is treated as a second layer, because this repo can host the backend and server-rendered UI cleanly, but the default admin shell is not module-pluggable in the same way as Laravel routes/views.

The result will feel native on web if you:

- keep URLs under the main app domain
- reuse Invoice Ninja layouts, auth, settings, branding, and translation patterns
- reuse existing PDF, email, document, and portal conventions
- keep module-owned code inside `Modules/Contracts`

## 2. Hard Constraint You Should Plan Around

This repository can fully support:

- Contracts backend domain
- Admin APIs
- Portal contract pages
- Public signing flow
- PDF/certificate generation
- Audit/versioning/reminders
- Contract automation hooks
- Renewal workflows
- White-labeling
- A polished admin contracts workspace built with Blade + Livewire + Vite assets

This repository does not automatically make new entities appear in the default Invoice Ninja admin SPA navigation.

That means:

- If your self-host uses the default Flutter web shell from `BaseController::flutterRoute()`, a module alone will not add native Flutter screens.
- If your self-host uses the optional React app, a backend module alone still will not add new React routes without client changes.

Best practical approach:

1. Build Contracts fully in the backend module now.
2. Expose a first-party-feeling admin workspace at `/contracts` using module views/components.
3. Add portal and public signing inside this repo.
4. If you later want Contracts inside the Flutter or React admin shell itself, treat that as a separate frontend project/fork.

## 3. Recommended Architecture

### 3.1 Module Root

Create:

```text
Modules/Contracts/
```

Recommended structure:

```text
Modules/Contracts/
  module.json
  composer.json
  config/config.php
  routes/api.php
  routes/web.php
  routes/client.php
  app/
    Models/
    Http/
      Controllers/
        Admin/
        Api/
        Portal/
        Public/
      Requests/
    Policies/
    Providers/
      ContractsServiceProvider.php
      RouteServiceProvider.php
    Services/
      ContractsService.php
      ContractTemplateService.php
      ContractClauseService.php
      ContractRenderService.php
      ContractPdfService.php
      ContractSigningService.php
      ContractAuditService.php
      ContractVersionService.php
      ContractReminderService.php
      ContractApprovalService.php
      ContractVariableResolver.php
      ContractLineItemResolver.php
    Repositories/
    Jobs/
    Mail/
    Notifications/
    Transformers/
    Enums/
    Events/
    Listeners/
    Livewire/
  database/
    migrations/
    factories/
    seeders/
  resources/
    views/
      admin/
      portal/
      public/
      pdf/
      email/
    assets/
      js/
      css/
  lang/
  tests/
```

### 3.2 `module.json`

Use custom metadata because Invoice Ninja already reads module metadata for menus/tabs via `Module::getCached()` in `MakesMenu`.

Suggested `module.json`:

```json
{
  "name": "Contracts",
  "alias": "contracts",
  "description": "Native-feeling contracts for Invoice Ninja",
  "keywords": ["contracts", "esign", "portal"],
  "priority": 0,
  "providers": [
    "Modules\\Contracts\\app\\Providers\\ContractsServiceProvider"
  ],
  "files": [],
  "sidebar": true,
  "views": ["client", "quote", "invoice", "project"],
  "icon": "file-signature",
  "active": 1
}
```

Notes:

- `sidebar` and `views` are not stock nwidart fields, but this app already expects module metadata fields in the cached module payload.
- Keep the module self-describing here rather than sprinkling hard-coded knowledge across core files.

## 4. Recommended UI Strategy

### 4.1 Admin UX

Recommended for now:

- Build a Contracts workspace with Blade + Livewire + Vite-powered editor components.
- Match existing Invoice Ninja design tokens, spacing, buttons, and typography from portal/admin assets where practical.
- Use a left sub-navigation inside the Contracts workspace:
  - Contracts
  - Templates
  - Clauses
  - Approvals
  - Audit
  - Settings

Why this is the best tradeoff:

- It is update-safe compared with forking the Flutter web admin.
- It lives inside the same Laravel app, auth, session, and branding context.
- It can still feel first-party if the route, layout, permissions, and styling are consistent.
- It avoids coupling your contracts rollout to a custom Flutter/React client fork on day one.

### 4.2 Portal UX

Use the existing portal patterns:

- contract list page
- contract show page
- sign page
- signed archive page
- download page

Portal integration is much cleaner because the client portal is already server-rendered in this repository.

### 4.3 Public Signing UX

Use a dedicated public route with token auth and a clean branded layout, similar in spirit to quote/invoice invitation routes.

## 5. Best Editor Choice

### Recommendation: Tiptap

Use `Tiptap` with a custom ProseMirror schema as the core contract editor.

Do not use GrapesJS as the primary editor.

Why Tiptap is the best fit:

- It stores structured JSON cleanly in `contract_json`.
- It produces predictable HTML that is easier to sanitize and render to PDF.
- It is much better for legal-document workflows than page-builder-style marketing editors.
- It supports custom nodes for signature blocks, merge variables, clause embeds, approval blocks, pricing tables, and signer markers.
- It handles rich text, tables, images, links, headings, lists, placeholders, and custom block extensions well.
- It is easier to keep responsive and maintainable than GrapesJS.
- It is a better long-term base for autosave, versioning, and structured validation.

Why not GrapesJS as the primary editor:

- It is stronger for drag-and-drop marketing/email layouts than for structured legal documents.
- It produces more builder-specific markup and more PDF fragility.
- It is harder to constrain for legal-document consistency and signature semantics.

Recommended editor architecture:

1. Tiptap editor state stored as canonical `contract_json`.
2. Server-side renderer converts `contract_json` to sanitized HTML for:
   - preview
   - emails
   - PDFs
   - public signing
3. `contract_body` stores the latest compiled HTML snapshot for fast rendering/search/debugging.
4. Custom Tiptap nodes:
   - `mergeVariable`
   - `clauseReference`
   - `signatureBlock`
   - `signerField`
   - `pricingTable`
   - `approvalStamp`
   - `pageBreak`
   - `attachmentList`

Recommended NPM packages:

```bash
npm install @tiptap/core @tiptap/starter-kit @tiptap/extension-placeholder @tiptap/extension-link @tiptap/extension-underline @tiptap/extension-image @tiptap/extension-text-align @tiptap/extension-table @tiptap/extension-table-row @tiptap/extension-table-cell @tiptap/extension-table-header @tiptap/extension-character-count @tiptap/suggestion
```

Optional:

```bash
npm install @tiptap/extension-drag-handle
```

### Editor layout

Use a 3-column contract editor:

- left rail: template blocks, clauses, variables, signer roles
- center: document editor
- right rail: metadata, preview mode, signers, approval routing, reminders

### Autosave

- debounce at 2 to 5 seconds
- save draft JSON and HTML snapshot
- store editor checksum
- create version only on meaningful changes or send/sign boundary transitions

## 6. Best PDF and E-Sign Stack

### Signature Capture

Use the already-present `signature_pad` dependency for drawn signatures.

Also support typed signatures with:

- signer full legal name
- font-rendered typed signature
- explicit consent checkbox
- timestamp
- IP
- user agent

### PDF Generation

Primary recommendation:

- Reuse Invoice Ninja’s existing PDF generation pipeline patterns.
- Add a dedicated `ContractPdfService` that renders module Blade HTML and converts it via the same underlying PDF engine strategy used by `CreateRawPdf`.

Why:

- lowest divergence from existing app behavior
- easiest to maintain in Docker
- reuses current PDF config and fallback behavior
- easier to keep update-safe

Use HTML-first rendering for both unsigned and signed contracts:

1. render contract HTML
2. inject resolved variables
3. inject signer evidence blocks and signatures
4. append certificate page
5. append audit summary page
6. convert full HTML to PDF

Use existing sanitization via `App\Services\Pdf\Purify`.

### Optional PDF Alternative

If you later need near-perfect CSS fidelity, add Browsershot/Chromium as an optional renderer, but do not make it the first implementation.

Why not first:

- higher Docker complexity
- more moving parts
- less aligned with current Invoice Ninja PDF path

### Recommended Composer additions

Only if needed after first pass:

```bash
composer require spatie/browsershot
```

No extra signature library is required for capture because `signature_pad` is already present in `package.json`.

## 6A. Payment Collection And Bank Connectivity

### Recommended default

For easiest customer setup, use:

- merchant connects Stripe once
- customer pays by card, Apple Pay, Google Pay, or bank account in the payment flow
- no customer Stripe account is required

This is the key product decision:

- your business should own the payment processor relationship
- your customers should only authorize payment or link a bank account
- customers should never need to create a Stripe account just to pay you

### Why this is the best default

Stripe’s ACH Direct Debit flow already supports bank-account collection and verification through Financial Connections, with fallback to manual entry and microdeposit verification.

That means:

- customers can link a bank account directly
- customers do not need a Stripe login/account
- your team gets a simpler support story
- it fits Invoice Ninja’s existing payment-gateway model better than introducing a separate bank-only processor first

### Important clarification about Plaid

Plaid is good for bank connection and account verification, but Plaid `Auth` is not itself the payment processor. Plaid’s docs say `Auth` must be used with a payment processor or payments partner, while `Transfer` is Plaid’s end-to-end money movement product and is US-only.

So for this Contracts module:

- use Stripe ACH + Financial Connections as the default bank-payment path
- optionally add Plaid later for enhanced bank-linking, balance checks, ownership verification, or treasury-style workflows
- only use Plaid `Transfer` as a later alternative if your business specifically wants to standardize on Plaid’s US-only money movement stack

### Recommended implementation path

Phase 1 default:

- Stripe Checkout or Payment Element for contract-linked payment collection
- Stripe for card payments
- Stripe for Apple Pay / Google Pay where available
- Stripe ACH / pay-by-bank for bank-account collection
- Stripe Financial Connections for instant bank verification when customer chooses bank payment
- fallback to manual bank entry + microdeposits where needed

Phase 2 optional:

- Plaid Auth + Stripe processor token flow
- Plaid Signal / Balance / Identity for risk reduction
- Plaid Transfer evaluation for US-only advanced bank-payment cases

### Contract payment schedule behavior

For signed contracts with payment schedules:

- upfront payment can generate an Invoice Ninja invoice payable via Stripe ACH
- upfront payment can also be payable by card / wallet / ACH depending on enabled gateway methods
- milestone/completion payments can generate invoices later from project or workflow triggers
- recurring renewals can generate future invoices or invoice schedules automatically

### Manual and offline payment tracking

You also want customers and staff to be able to handle non-processor payment methods such as:

- check
- cash
- bank transfer/wire
- external POS payment

Best implementation path:

- let Contract payment schedules create normal Invoice Ninja invoices
- let those invoices be paid through Invoice Ninja’s existing gateway architecture when paying online
- let staff mark invoices paid manually using existing Invoice Ninja payment records for check/cash/offline settlement
- sync contract payment-schedule status from the linked invoice/payment state

This is better than inventing a separate contract-only payment ledger.

### Final payment recommendation

For this platform, the best customer-friendly setup is:

- Stripe as the primary processor
- Invoice Ninja’s existing payment gateway and payment-record model reused underneath
- customers never create Stripe accounts
- customers can pay by card, Apple Pay, Google Pay, or linked bank account
- staff can still record check and offline payments manually

### UX recommendation

On the customer side, label this clearly as:

- Pay by bank
- Bank transfer (ACH)
- Link your bank account

Do not surface internal processor branding as the main choice unless legally or operationally required.

## 7. Database Design

### 7.1 Entity Conventions

Mirror Invoice Ninja entity conventions where practical:

- `account_id`
- `company_id`
- `user_id` or `created_by`
- `assigned_user_id` when useful
- `is_deleted`
- `deleted_at`
- `created_at`
- `updated_at`
- hashed ID support through `MakesHash`
- `public_token` for external signing

### 7.2 Main Tables

#### `contracts`

Suggested columns:

- `id`
- `account_id`
- `company_id`
- `client_id` nullable
- `contact_id` nullable
- `project_id` nullable
- `quote_id` nullable
- `invoice_id` nullable
- `template_id` nullable
- `created_by`
- `updated_by` nullable
- `assigned_user_id` nullable
- `contract_number`
- `title`
- `status_id`
- `approval_status` nullable
- `public_token`
- `contract_json` json
- `contract_body` longText
- `variables_snapshot` json nullable
- `line_items_snapshot` json nullable
- `theme_snapshot` json nullable
- `expires_at` nullable
- `sent_at` nullable
- `viewed_at` nullable
- `completed_at` nullable
- `signed_at` nullable
- `declined_at` nullable
- `cancelled_at` nullable
- `pdf_path` nullable
- `signed_pdf_path` nullable
- `checksum`
- `signed_checksum` nullable
- `is_deleted` default false
- soft deletes
- timestamps

#### `contract_templates`

- `id`
- `account_id`
- `company_id`
- `user_id`
- `name`
- `description` nullable
- `category` nullable
- `contract_json`
- `contract_body`
- `header_html` nullable
- `footer_html` nullable
- `default_variables` json nullable
- `theme` json nullable
- `is_active`
- `is_deleted`
- soft deletes
- timestamps

#### `contract_clauses`

- `id`
- `account_id`
- `company_id`
- `user_id`
- `name`
- `slug`
- `category` nullable
- `tags` json nullable
- `clause_json` json nullable
- `clause_body` longText
- `version_number`
- `is_active`
- `is_deleted`
- soft deletes
- timestamps

#### `contract_signers`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `client_contact_id` nullable
- `user_id` nullable
- `name`
- `email`
- `role`
- `sign_order`
- `status`
- `requires_signature`
- `required_checkbox_text` nullable
- `access_token`
- `viewed_at` nullable
- `signed_at` nullable
- `declined_at` nullable
- `ip_address` nullable
- `user_agent` nullable
- `signature_type` nullable
- `signature_image_path` nullable
- `typed_signature` nullable
- `signature_hash` nullable
- timestamps

#### `contract_signatures`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `contract_signer_id`
- `signature_type`
- `signature_image_path` nullable
- `typed_signature` nullable
- `consent_text`
- `consented_at`
- `ip_address`
- `user_agent`
- `checksum`
- `certificate_payload` json
- timestamps

#### `contract_audit_logs`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `contract_version_id` nullable
- `actor_type`
- `actor_id` nullable
- `event_type`
- `ip_address` nullable
- `user_agent` nullable
- `old_values` json nullable
- `new_values` json nullable
- `meta` json nullable
- `created_at`

Make this append-only.

#### `contract_versions`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `version_number`
- `status_snapshot`
- `contract_json`
- `contract_body`
- `variables_snapshot` json nullable
- `line_items_snapshot` json nullable
- `checksum`
- `created_by`
- `change_summary` nullable
- timestamps

#### `contract_attachments`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `document_id` nullable
- `name`
- `path`
- `mime_type`
- `size`
- `visibility`
- timestamps

#### `contract_reminders`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `type`
- `scheduled_for`
- `sent_at` nullable
- `target_email`
- `status`
- `meta` json nullable
- timestamps

#### `contract_comments`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `user_id`
- `body`
- `mentions` json nullable
- `is_internal`
- `created_at`
- `updated_at`

Use this for:

- internal notes
- approval discussion
- lightweight activity discussion
- mention notifications

#### `contract_packages`

- `id`
- `account_id`
- `company_id`
- `user_id`
- `name`
- `description` nullable
- `package_json`
- `is_active`
- timestamps

Package payload should support:

- clause references
- default pricing blocks
- invoice schedule definitions
- signer-role presets
- onboarding form presets

#### `contract_payment_schedules`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `label`
- `sequence`
- `schedule_type`
- `amount_type`
- `amount`
- `due_rule`
- `trigger_event` nullable
- `invoice_id` nullable
- `status`
- `meta` json nullable
- timestamps

#### `contract_renewals`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `renewal_frequency`
- `renewal_date`
- `renewal_notice_days`
- `auto_create_draft`
- `last_renewed_at` nullable
- `next_contract_id` nullable
- `status`
- timestamps

#### `contract_onboarding_forms`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `title`
- `schema` json
- `is_required`
- `visibility`
- timestamps

#### `contract_onboarding_submissions`

- `id`
- `account_id`
- `company_id`
- `contract_onboarding_form_id`
- `contract_id`
- `client_contact_id`
- `submission` json
- `submitted_at`
- timestamps

#### `contract_webhook_events`

- `id`
- `account_id`
- `company_id`
- `contract_id`
- `event_name`
- `payload` json
- `dispatched_at` nullable
- `status`
- `response_code` nullable
- timestamps

### 7.3 Optional Tables

Recommended additions for full workflow:

- `contract_approvals`
- `contract_clause_versions`
- `contract_template_versions`
- `contract_comments`
- `contract_packages`
- `contract_package_items`
- `contract_payment_schedules`
- `contract_renewals`
- `contract_onboarding_forms`
- `contract_onboarding_submissions`
- `contract_webhook_events`

## 8. Status Model

Use integer constants or backed enums.

Recommended statuses:

- `DRAFT = 1`
- `INTERNAL_REVIEW = 2`
- `APPROVED = 3`
- `SENT = 4`
- `VIEWED = 5`
- `SIGNED = 6`
- `DECLINED = 7`
- `EXPIRED = 8`
- `CANCELLED = 9`

Completion rule:

- contract becomes fully signed only when all required signers have `SIGNED`
- if signing order is enabled, only the next signer can access signing controls

## 9. Example Migration

Create migrations inside the module, not under the root app.

Example for `contracts`:

```php
Schema::create('contracts', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('account_id')->index();
    $table->unsignedInteger('company_id')->index();
    $table->unsignedBigInteger('client_id')->nullable()->index();
    $table->unsignedBigInteger('contact_id')->nullable()->index();
    $table->unsignedBigInteger('project_id')->nullable()->index();
    $table->unsignedBigInteger('quote_id')->nullable()->index();
    $table->unsignedBigInteger('invoice_id')->nullable()->index();
    $table->unsignedBigInteger('template_id')->nullable()->index();
    $table->unsignedBigInteger('created_by')->index();
    $table->unsignedBigInteger('updated_by')->nullable()->index();
    $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
    $table->string('contract_number')->index();
    $table->string('title');
    $table->unsignedSmallInteger('status_id')->default(1)->index();
    $table->string('public_token', 64)->unique();
    $table->json('contract_json');
    $table->longText('contract_body');
    $table->json('variables_snapshot')->nullable();
    $table->json('line_items_snapshot')->nullable();
    $table->json('theme_snapshot')->nullable();
    $table->timestamp('expires_at')->nullable()->index();
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('viewed_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamp('signed_at')->nullable();
    $table->timestamp('declined_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->string('pdf_path')->nullable();
    $table->string('signed_pdf_path')->nullable();
    $table->string('checksum', 128)->index();
    $table->string('signed_checksum', 128)->nullable();
    $table->boolean('is_deleted')->default(false)->index();
    $table->softDeletes();
    $table->timestamps();
});
```

Example for `contract_signers`:

```php
Schema::create('contract_signers', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('account_id')->index();
    $table->unsignedInteger('company_id')->index();
    $table->unsignedBigInteger('contract_id')->index();
    $table->unsignedBigInteger('client_contact_id')->nullable()->index();
    $table->unsignedBigInteger('user_id')->nullable()->index();
    $table->string('name');
    $table->string('email')->index();
    $table->string('role')->nullable();
    $table->unsignedSmallInteger('sign_order')->default(1)->index();
    $table->string('status')->default('pending')->index();
    $table->boolean('requires_signature')->default(true);
    $table->string('required_checkbox_text')->nullable();
    $table->string('access_token', 64)->unique();
    $table->timestamp('viewed_at')->nullable();
    $table->timestamp('signed_at')->nullable();
    $table->timestamp('declined_at')->nullable();
    $table->string('ip_address', 64)->nullable();
    $table->text('user_agent')->nullable();
    $table->string('signature_type')->nullable();
    $table->string('signature_image_path')->nullable();
    $table->string('typed_signature')->nullable();
    $table->string('signature_hash', 128)->nullable();
    $table->timestamps();
});
```

## 10. Laravel Models

Recommended models:

- `Contract`
- `ContractTemplate`
- `ContractClause`
- `ContractSigner`
- `ContractSignature`
- `ContractAuditLog`
- `ContractVersion`
- `ContractAttachment`
- `ContractReminder`
- `ContractApproval`

Recommended model traits:

- `MakesHash`
- `SoftDeletes`
- `Filterable` if you want Invoice Ninja-style filtering
- `Searchable` if you want Elastic/Scout support

Recommended `Contract` relations:

- `belongsTo(Client::class)`
- `belongsTo(ClientContact::class, 'contact_id')`
- `belongsTo(Invoice::class)`
- `belongsTo(Quote::class)`
- `belongsTo(Project::class)`
- `belongsTo(ContractTemplate::class, 'template_id')`
- `belongsTo(User::class, 'created_by')`
- `belongsTo(User::class, 'updated_by')`
- `hasMany(ContractSigner::class)`
- `hasMany(ContractSignature::class)`
- `hasMany(ContractAuditLog::class)`
- `hasMany(ContractVersion::class)`
- `hasMany(ContractAttachment::class)`
- `hasMany(ContractReminder::class)`

## 11. Controllers

### Admin

- `Admin/ContractController`
- `Admin/ContractTemplateController`
- `Admin/ContractClauseController`
- `Admin/ContractAuditController`
- `Admin/ContractApprovalController`

### API

- `Api/ContractController`
- `Api/ContractTemplateController`
- `Api/ContractClauseController`
- `Api/ContractActionController`

### Portal

- `Portal/ContractController`

### Public

- `Public/ContractSigningController`

### Supporting

- `Admin/ContractCommentController`
- `Admin/ContractPackageController`
- `Admin/ContractAnalyticsController`
- `Api/ContractWebhookController`
- `Api/ContractPaymentScheduleController`
- `Api/ContractRenewalController`
- `Portal/ContractOnboardingController`

Recommended action endpoints:

- `send`
- `preview`
- `generatePdf`
- `downloadPdf`
- `archive`
- `remind`
- `approve`
- `decline`
- `restoreVersion`
- `duplicateRenewalDraft`
- `runAutomation`
- `submitOnboarding`

## 12. Services

This module should be service-heavy.

Core services:

- `ContractsService`
  - create/update/send/cancel/state transitions
- `ContractRenderService`
  - converts JSON to sanitized HTML
- `ContractVariableResolver`
  - resolves `{{company.name}}` style variables
- `ContractLineItemResolver`
  - injects quote/invoice line items
- `ContractPdfService`
  - renders unsigned/signed PDFs and certificate pages
- `ContractSigningService`
  - validates signer access, stores signature evidence, completes sign flow
- `ContractAuditService`
  - append-only logging
- `ContractVersionService`
  - stores/restores snapshots
- `ContractReminderService`
  - reminder scheduling and dispatch
- `ContractApprovalService`
  - internal review and approval path
- `ContractTemplateService`
  - create/update/apply templates
- `ContractClauseService`
  - manage and insert clauses
- `ContractPackageService`
  - preload reusable contract packages
- `ContractRenewalService`
  - renewal reminders and renewal-draft generation
- `ContractAutomationService`
  - signed-contract downstream automation
- `ContractAnalyticsService`
  - dashboard and reporting metrics

## 13. Merge Variable Engine

Do not invent a separate ad-hoc replacement layer.

Build `ContractVariableResolver` so it reuses the same mental model as `TemplateEngine` and `HtmlEngine`, but returns contract-safe values.

Recommended variable groups:

- company
- client
- contact
- quote
- invoice
- project
- contract
- payment_terms
- payment_schedule
- line_items

Example mapping:

```php
[
    '{{company.name}}' => $company->present()->name(),
    '{{company.email}}' => $company->settings->email,
    '{{client.name}}' => $client?->present()->name() ?? '',
    '{{contact.first_name}}' => $contact?->first_name ?? '',
    '{{quote.number}}' => $quote?->number ?? '',
    '{{invoice.total}}' => $invoice ? Number::formatMoney($invoice->amount, $company) : '',
    '{{project.name}}' => $project?->name ?? '',
    '{{contract.start_date}}' => $contract->start_date?->format('Y-m-d') ?? '',
]
```

Rules:

- resolve on preview
- resolve on send
- snapshot resolved values into `variables_snapshot`
- resolve again only when allowed in draft/review state
- signed contracts should not silently change after signing

## 14. Line Item Injection

Do not embed raw invoice tables directly in editor content.

Use a custom node or placeholder such as:

- `{{quote.line_items}}`
- `{{invoice.line_items}}`

At render time:

1. resolve related entity
2. normalize line items
3. build a module-owned Blade partial
4. store rendered snapshot into `line_items_snapshot`

Columns:

- item
- description
- quantity
- unit price
- tax
- line total
- subtotal
- grand total

## 15. Clause Library

Implement clauses as reusable structured content, not just text snippets.

Recommended features:

- category
- tags
- active/inactive
- version number
- clause JSON
- clause HTML snapshot
- insert into editor at cursor
- update existing clause reference in draft contracts
- snapshot clause content into sent/signed versions

Use one of two modes:

1. embedded snapshot mode
2. linked reference mode before send, frozen snapshot after send

That gives you flexibility without breaking immutable signed contracts.

## 15A. Contract Packages

Add reusable contract packages as a layer above clauses and templates.

Examples:

- Website Build Package
- SEO Package
- SaaS Agreement
- Maintenance Plan

Each package can preload:

- clause references
- pricing blocks
- invoice schedule defaults
- signer role defaults
- renewal settings
- onboarding forms

Recommended usage:

1. user chooses package
2. package applies base template
3. package injects clause references and defaults
4. user customizes before send

This should be implemented as data composition, not hard-coded branching.

## 16. E-Sign Implementation

### Public signing flow

Use:

- `/contract/sign/{token}` for GET
- `/contract/sign/{token}` for POST

Signing flow:

1. validate contract + signer token
2. validate signer order
3. display contract, signer identity, and consent text
4. capture signature or typed signature
5. capture checkbox consent
6. store IP + UA + timestamp
7. generate signer evidence hash
8. append audit event
9. if all required signers complete, finalize signed PDF and mark immutable

### Signature evidence to store

- signer id
- signer name
- signer email
- access token hash
- signature image path or typed signature text
- consent text
- consent timestamp
- IP
- user agent
- contract checksum before sign
- signer evidence checksum

### Immutability rule

After final required signature:

- lock editing
- lock variables snapshot
- lock clause snapshots
- lock pricing/payment-schedule snapshot
- freeze compiled HTML snapshot
- generate final signed PDF
- generate certificate page
- store `signed_checksum`

### Immutable signed artifact storage

This is legally important and should be explicit in the implementation.

When a contract is fully signed, freeze and retain:

- canonical editor JSON at sign time
- compiled sanitized HTML
- resolved variables snapshot
- line item snapshot
- pricing/payment schedule snapshot
- final signed PDF
- certificate page payload
- signature evidence payload
- final combined checksum

Do not regenerate signed output from live relational data after completion.

## 17. Multi-Signer Support

Model it through `contract_signers`.

Rules:

- allow internal and external signers
- allow `sign_order`
- allow `required` vs optional
- allow counter-sign flow
- mark contract complete only when all required signers are signed

Recommended statuses per signer:

- `pending`
- `sent`
- `viewed`
- `signed`
- `declined`
- `expired`
- `skipped`

## 17A. Recurring Contracts And Renewals

Support:

- annual renewal
- manual renewal
- reminder before renewal
- duplicate to renewal draft
- renewal approval/send/sign cycle

Recommended flow:

1. active signed contract carries renewal settings
2. reminder job triggers before renewal date
3. system creates renewal draft from frozen signed snapshot
4. admin updates pricing/terms if needed
5. new renewal contract gets its own version/signature history

Do not mutate the original signed contract for renewals.

## 18. Audit Trail

Do not try to overload the core `activities` table for contract evidence.

Use a dedicated `contract_audit_logs` table.

You may also mirror key contract events into the core activity feed later, but the legal audit source of truth should stay in the module.

Track:

- created
- edited
- status changed
- version created
- sent
- viewed
- reminder sent
- downloaded
- signed
- declined
- expired
- cancelled
- approval requested
- approval granted
- approval rejected

Also add a user-facing timeline/activity feed on the contract detail view backed by:

- audit logs
- internal comments
- mentions
- approval events
- automation events

Append-only policy:

- no update/delete endpoints
- only inserts
- corrections are new audit rows

## 19. Versioning

Rules:

- editing a draft updates current working copy
- sending creates version snapshot `v1`
- editing after send clones to next draft version `v2`
- signed contracts become immutable
- restoring a version creates a new current draft, not destructive rollback

Version trigger points:

- before send
- on edit after send
- before approval transition
- before final sign

## 20. Email System

Use Invoice Ninja’s existing mail transport and notification patterns.

Create module mailables or notifications for:

- contract sent
- reminder
- viewed
- signed
- declined
- expired
- internal approval request
- internal approval completed
- internal comment mention
- renewal upcoming
- onboarding form submitted

Email templates should live in:

```text
Modules/Contracts/resources/views/email/
```

Also add template settings in module config or company settings extension if you want per-company editable content.

## 21. Customer Portal Integration

This is one of the cleanest parts to integrate in this repo.

### Routes

Add module portal routes similar to `routes/client.php` patterns:

- `client/contracts`
- `client/contracts/{contract}`
- `client/contracts/{contract}/download`
- `client/contracts/{contract}/sign`

### Sidebar

You have two options:

1. Minimal core touch:
   - add a small extension hook in `PortalComposer::sidebarMenu()` once, then let the module register itself
2. Fully isolated route without sidebar entry:
   - works, but feels less native

I recommend one small core hook here because it pays off long-term.

Example hook approach:

- add a `contracts_portal_menu()` resolver or event/listener pattern in the core composer once
- module contributes its item without further core edits

### Portal dashboard widgets

Recommended client-facing widgets:

- Contracts
- Invoices
- Projects
- Payments
- Files

Contracts-specific widget states:

- pending signature
- recently signed
- awaiting onboarding
- renewal due soon

## 22. API Implementation

Add module routes under the existing authenticated API prefix.

Recommended endpoints:

- `GET /api/v1/contracts`
- `POST /api/v1/contracts`
- `GET /api/v1/contracts/{id}`
- `PUT /api/v1/contracts/{id}`
- `DELETE /api/v1/contracts/{id}`
- `POST /api/v1/contracts/{id}/send`
- `POST /api/v1/contracts/{id}/generate-pdf`
- `POST /api/v1/contracts/{id}/remind`
- `POST /api/v1/contracts/{id}/approve`
- `POST /api/v1/contracts/{id}/decline`
- `GET /api/v1/contracts/{id}/analytics`
- `POST /api/v1/contracts/{id}/duplicate-renewal-draft`
- `POST /api/v1/contracts/{id}/payment-schedules`
- `POST /api/v1/contracts/{id}/run-automation`
- `POST /api/v1/contracts/{id}/comments`
- `POST /api/v1/contracts/{id}/onboarding-forms`
- `GET /contract/sign/{token}`
- `POST /contract/sign/{token}`

Follow Invoice Ninja controller style:

- request classes for validation
- policy checks
- transformers for API payloads
- `Filterable` support for search/filter behavior

## 23. Permissions

Create policies and permission keys for:

- `view_contract`
- `create_contract`
- `edit_contract`
- `delete_contract`
- `send_contract`
- `sign_contract`
- `view_contract_audit`
- `manage_contract_templates`
- `manage_contract_clauses`
- `manage_contract_approvals`
- `comment_on_contract`
- `manage_contract_packages`
- `manage_contract_renewals`
- `manage_contract_analytics`

Implementation note:

- Invoice Ninja permissions are role-driven and entity-specific.
- Keep Contracts permissions module-owned and map them in policy checks.
- Avoid hacking existing invoice/quote permissions to imply contract permissions.

## 24. Approval Workflow

Start simple:

- draft
- internal review
- approved
- sent
- signed

Recommended `contract_approvals` table:

- approver user
- contract id
- step order
- status
- requested_at
- acted_at
- notes

Rules:

- only approved contracts can be sent
- edits after approval return to internal review
- approval actions create audit rows

Approval UX should support:

- internal comments
- @mentions
- request changes notes
- timeline visibility

## 25. Attachments

Do not create a second unrelated file-storage stack.

Reuse Invoice Ninja document storage patterns where possible.

Best approach:

- upload to existing document storage
- link `document_id` in `contract_attachments`
- optionally copy visibility flags

This keeps S3/local/private storage behavior aligned with the rest of the app.

## 25A. Payment Schedule Support

Support structured payment schedules like:

- 50% upfront
- 25% milestone
- 25% completion

The schedule should be stored independently from raw document text so it can:

- render in the contract
- freeze in the signed artifact
- generate invoices automatically
- tie into project milestones later

Recommended automation:

- upfront schedule item can create invoice immediately on sign
- milestone items can create draft invoices when internal trigger events occur
- completion item can create final invoice when project closes

## 26. Search and Filters

Support filters like the existing entity controllers do.

Recommended filters:

- status
- client_id
- contact_id
- quote_id
- invoice_id
- project_id
- created_by
- expires_before
- expires_after
- signed_before
- signed_after
- title
- contract_number

If you want parity with the rest of the app, add a `ContractFilters` class and optional Scout indexing.

## 26A. Contract Analytics

Recommended analytics metrics:

- viewed but unsigned count
- average time to sign
- signed revenue
- contracts expiring soon
- quote-to-sign conversion rate
- renewal conversion rate

Recommended delivery:

- module dashboard cards
- API metrics endpoint
- later report export support

## 27. White-Labeling Guidance

### Already available

Current repo already supports branding through:

- [config/branding.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/config/branding.php)
- company settings like `portal_custom_css`, `portal_custom_head`, `portal_custom_footer`
- company logo settings
- email template wrapper settings

### Recommended white-label approach

Use environment/config first, company settings second, view overrides last.

#### App name and support identity

Prefer:

- `APP_NAME`
- `BRAND_COMPANY_NAME`
- `BRAND_SUPPORT_EMAIL`
- `BRAND_SUPPORT_NAME`
- `BRAND_WEBSITE_URL`
- `BRAND_SUPPORT_URL`
- `BRAND_TERMS_URL`
- `BRAND_PRIVACY_URL`

#### Logo and favicon

Prefer:

- `BRAND_LOGO_LIGHT`
- `BRAND_LOGO_DARK`
- `BRAND_APP_LOGO`
- `BRAND_FAVICON`

Also keep company-specific uploaded logos for tenant branding.

#### Custom colors/theme

Best update-safe method:

- add a module/admin CSS file
- use CSS variables
- inject via layout section or Vite entry
- keep overrides scoped under `.contracts-app`

#### Custom login page

Safest path:

- override only the login-related Blade views you actually use
- keep logic/controllers intact

Relevant current views include:

- `resources/views/themes/ninja2020/auth/*`
- `resources/views/auth/contact_login.blade.php`

#### Email templates/footer

Prefer:

- use existing email wrapper mechanisms
- add module-specific mail templates for contracts
- avoid modifying shared invoice mail templates unless the brand change is global

#### PDF branding

Use:

- company logo
- company settings
- module-owned PDF templates under `Modules/Contracts/resources/views/pdf`

#### Navigation labels/dashboard widgets

This is where update-safety becomes trickier in the main SPA.

Best near-term approach:

- Contracts module has its own workspace route and module-specific dashboard cards
- small launcher entry or quick-link in existing views

### Files/configs to modify for white-labeling

Low-risk:

- [config/branding.php](/abs/path/c:/Users/Shild/Downloads/invoiceninja/config/branding.php)
- `.env`
- tenant company settings
- module-owned views/assets

Higher-risk:

- core layouts
- SPA assets
- Flutter web bundle

## 28. Invoice Ninja Areas to Hook Into

Safe hooks:

- module system in `config/modules.php`
- root authenticated API group pattern in `routes/api.php`
- portal route pattern in `routes/client.php`
- merge variable approach from `TemplateEngine` and `HtmlEngine`
- PDF pipeline pattern from `CreateRawPdf`
- sanitization from `App\Services\Pdf\Purify`
- document storage patterns
- auth guards and policies

One-time small core hooks that are worth it:

- portal sidebar registration hook
- admin launcher/menu hook if you want a visible Contracts entry without SPA fork
- dashboard widget registration hook
- webhook event registration hook

## 29. Core Files You Should Avoid Touching

Avoid direct feature logic changes in:

- `app/Models/Invoice.php`
- `app/Models/Quote.php`
- `app/Http/Controllers/InvoiceController.php`
- `app/Http/Controllers/QuoteController.php`
- `app/Jobs/Entity/CreateRawPdf.php`
- `app/Utils/TemplateEngine.php`
- `app/Utils/HtmlEngine.php`
- `routes/api.php`
- `routes/client.php`

Only make minimal extension-hook changes there if absolutely necessary.

If you touch core, keep it limited to:

- registration hook
- menu hook
- event hook

Not feature implementation.

## 30. Suggested Minimal Core Touches

If you are willing to make a few tiny core edits, these are the only ones I would endorse:

1. Add a generic menu registration hook for portal/admin sidebars.
2. Add a generic module route loader for `routes/client.php` if module client routes are not already auto-loaded by your provider.
3. Add a generic dashboard widget registration hook if you want module widgets in a shared dashboard surface.
4. Add a generic webhook event registration hook if you want contract events exposed consistently.

Do not put Contracts business logic in core.

## 30A. Webhook And Automation Events

Add first-party webhook/automation events early:

- `contract.created`
- `contract.sent`
- `contract.viewed`
- `contract.signed`
- `contract.expired`
- `contract.declined`

These should later be consumable by:

- n8n
- Zapier
- Make
- AI automations
- Slack/Discord notifications

Implementation recommendation:

1. fire module domain events internally
2. map them to webhook payload builders
3. optionally mirror them into Invoice Ninja-style webhook dispatch infrastructure

Also add downstream automation triggers such as:

- signed contract creates project
- signed contract creates tasks
- signed contract assigns users
- signed contract creates first invoice
- signed contract opens onboarding form

Keep automation rules module-owned and data-driven.

## 31. Exact Build Steps

### Module scaffold

```bash
php artisan module:make Contracts
php artisan module:enable Contracts
composer dump-autoload
```

### Generate module classes

```bash
php artisan module:make-model Contract Contracts
php artisan module:make-model ContractTemplate Contracts
php artisan module:make-model ContractClause Contracts
php artisan module:make-model ContractSigner Contracts
php artisan module:make-model ContractSignature Contracts
php artisan module:make-model ContractAuditLog Contracts
php artisan module:make-model ContractVersion Contracts
php artisan module:make-model ContractAttachment Contracts
php artisan module:make-model ContractReminder Contracts
php artisan module:make-controller Api/ContractController Contracts
php artisan module:make-controller Portal/ContractController Contracts
php artisan module:make-controller Public/ContractSigningController Contracts
php artisan module:make-migration create_contracts_table Contracts
php artisan module:make-migration create_contract_templates_table Contracts
php artisan module:make-migration create_contract_clauses_table Contracts
php artisan module:make-migration create_contract_signers_table Contracts
php artisan module:make-migration create_contract_signatures_table Contracts
php artisan module:make-migration create_contract_audit_logs_table Contracts
php artisan module:make-migration create_contract_versions_table Contracts
php artisan module:make-migration create_contract_attachments_table Contracts
php artisan module:make-migration create_contract_reminders_table Contracts
```

### Frontend packages

```bash
npm install @tiptap/core @tiptap/starter-kit @tiptap/extension-placeholder @tiptap/extension-link @tiptap/extension-underline @tiptap/extension-image @tiptap/extension-text-align @tiptap/extension-table @tiptap/extension-table-row @tiptap/extension-table-cell @tiptap/extension-table-header @tiptap/extension-character-count @tiptap/suggestion
```

Optional:

```bash
composer require spatie/browsershot
```

### Migrate and build

```bash
php artisan migrate
npm run build
```

## 32. Phased Delivery Plan

### Phase 1

- module scaffold
- migrations
- models
- policies
- API CRUD
- admin contracts list/create/edit/view
- Tiptap editor
- template manager
- clause library
- comments/timeline scaffold
- immutable artifact fields scaffold

### Phase 2

- public signing
- multi-signer flow
- signed PDF generation
- audit logs
- versioning
- reminders
- webhook events
- payment schedules
- signed artifact freezing

### Phase 3

- portal integration
- approval workflow
- dashboard widgets
- deeper branding pass
- packages
- renewal workflows
- project/invoice/task automation
- onboarding forms

### Phase 4

- optional React/Flutter admin-shell integration
- optional advanced clause approvals
- optional Elastic search indexing
- analytics
- AI clause/contract generation

## 33. Upgrade-Safe Rules

Follow these rules strictly:

1. All Contracts business logic lives in `Modules/Contracts`.
2. Reuse core services instead of copying them.
3. Add only extension hooks to core, not feature code.
4. Prefer company settings, config, and module views over core view rewrites.
5. Snapshot resolved variables before sign so signed artifacts do not drift.
6. Keep PDF rendering HTML-first and deterministic.
7. Keep audit logs append-only.
8. Keep signed versions immutable.
9. Store canonical structured editor JSON, not just raw HTML.
10. Treat Flutter/React admin-shell integration as optional frontend work, not a backend-module assumption.
11. Freeze all legally relevant signed artifacts rather than regenerating from mutable business data.
12. Add webhook and automation events as domain events, not ad-hoc controller side effects.

## 34. What I Would Build First In This Repo

If starting implementation now, I would build in this order:

1. `Modules/Contracts` scaffold and service provider
2. migrations and models
3. policies and request validation
4. `ContractVariableResolver`
5. `ContractRenderService`
6. admin CRUD with Blade/Livewire
7. Tiptap editor with autosave
8. templates and clauses
9. comments/timeline
10. public signing flow
11. signed PDF/certificate generation
12. immutable artifact freezing
13. portal contract pages
14. payment schedules and automation hooks
15. reminders, approvals, dashboard cards

## 35. Final Recommendation

The best architecture for your goals is:

- `Modules/Contracts` for all backend/domain logic
- Tiptap for the editor
- existing Invoice Ninja PDF pipeline patterns for PDF output
- existing `signature_pad` for signature capture
- dedicated audit/version/signer tables
- contract packages, renewals, comments, onboarding, payment schedules, and automation hooks as first-class module features
- portal/public signing in Blade/Livewire
- a server-rendered admin Contracts workspace now
- optional SPA-shell integration later if you decide to own the Flutter/React frontend too

That gives you the most native-feeling, maintainable, update-safe path available in this codebase.
