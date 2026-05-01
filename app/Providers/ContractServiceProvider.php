<?php

namespace App\Providers;

use App\Models\Contract;
use App\Observers\ContractObserver;
use App\Policies\ContractPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

class ContractServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Contract::class => ContractPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Contract::observe(ContractObserver::class);

        $this->registerApiRoutes();
        $this->registerPortalRoutes();
        $this->registerPublicRoutes();

        // Register the expiry command with the scheduler
        Schedule::command('contracts:expire')->hourly();
    }

    // ───────────────────────────────────────────────────────────────────────────────

    private function registerApiRoutes(): void
    {
        Route::group([
            'middleware' => ['throttle:api', 'token_auth', 'valid_json', 'locale'],
            'prefix'     => 'api/v1',
            'as'         => 'api.',
        ], function () {
            // Contract resources
            Route::apiResource('contracts', \App\Http\Controllers\ContractController::class);
            Route::post('contracts/bulk', [\App\Http\Controllers\ContractController::class, 'bulk'])->name('contracts.bulk');

            // Contract actions
            Route::post('contracts/{contract}/send',          [\App\Http\Controllers\ContractController::class, 'send'])->name('contracts.send');
            Route::post('contracts/{contract}/approve',       [\App\Http\Controllers\ContractController::class, 'approve'])->name('contracts.approve');
            Route::post('contracts/{contract}/cancel',        [\App\Http\Controllers\ContractController::class, 'cancel'])->name('contracts.cancel');
            Route::post('contracts/{contract}/counter-sign',  [\App\Http\Controllers\ContractController::class, 'counterSign'])->name('contracts.counter_sign');
            Route::post('contracts/{contract}/new-version',   [\App\Http\Controllers\ContractController::class, 'newVersion'])->name('contracts.new_version');
            Route::post('contracts/{contract}/generate-pdf',  [\App\Http\Controllers\ContractController::class, 'generatePdf'])->name('contracts.generate_pdf');
            Route::get('contracts/{contract}/download',       [\App\Http\Controllers\ContractController::class, 'downloadPdf'])->name('contracts.download');
            Route::get('contracts/{contract}/download-signed',[\App\Http\Controllers\ContractController::class, 'downloadSignedPdf'])->name('contracts.download_signed');
            Route::get('contracts/{contract}/audit-log',      [\App\Http\Controllers\ContractController::class, 'auditLog'])->name('contracts.audit_log');
            Route::post('contracts/{contract}/preview',       [\App\Http\Controllers\ContractController::class, 'preview'])->name('contracts.preview');

            // Signer management
            Route::post('contracts/{contract}/signers',               [\App\Http\Controllers\ContractController::class, 'addSigner'])->name('contracts.signers.add');
            Route::delete('contracts/{contract}/signers/{signer}',    [\App\Http\Controllers\ContractController::class, 'removeSigner'])->name('contracts.signers.remove');

            // Conversion helpers
            Route::post('contracts/from-quote/{quote}',   [\App\Http\Controllers\ContractController::class, 'fromQuote'])->name('contracts.from_quote');
            Route::post('contracts/from-invoice/{invoice}', [\App\Http\Controllers\ContractController::class, 'fromInvoice'])->name('contracts.from_invoice');

            // Variable list
            Route::get('contracts/variables', [\App\Http\Controllers\ContractController::class, 'variables'])->name('contracts.variables');

            // Contract templates
            Route::apiResource('contract_templates', \App\Http\Controllers\ContractTemplateController::class);
            Route::post('contract_templates/bulk',                              [\App\Http\Controllers\ContractTemplateController::class, 'bulk'])->name('contract_templates.bulk');
            Route::get('contract_templates/categories',                         [\App\Http\Controllers\ContractTemplateController::class, 'categories'])->name('contract_templates.categories');
            Route::post('contract_templates/{contract_template}/clone',         [\App\Http\Controllers\ContractTemplateController::class, 'clone'])->name('contract_templates.clone');
            Route::post('contract_templates/{contract_template}/create-contract',[\App\Http\Controllers\ContractTemplateController::class, 'createContractFromTemplate'])->name('contract_templates.create_contract');
            Route::post('contract_templates/from-contract/{contract}',          [\App\Http\Controllers\ContractTemplateController::class, 'cloneContractToTemplate'])->name('contract_templates.from_contract');

            // Clause library
            Route::get('contract_clauses',              [\App\Http\Controllers\ContractClauseController::class, 'index'])->name('contract_clauses.index');
            Route::post('contract_clauses',             [\App\Http\Controllers\ContractClauseController::class, 'store'])->name('contract_clauses.store');
            Route::put('contract_clauses/{contractClause}',  [\App\Http\Controllers\ContractClauseController::class, 'update'])->name('contract_clauses.update');
            Route::delete('contract_clauses/{contractClause}', [\App\Http\Controllers\ContractClauseController::class, 'destroy'])->name('contract_clauses.destroy');
            Route::get('contract_clauses/categories',   [\App\Http\Controllers\ContractClauseController::class, 'categories'])->name('contract_clauses.categories');
        });
    }

    private function registerPortalRoutes(): void
    {
        Route::group([
            'middleware' => ['auth:contact', 'locale', 'domain_db', 'check_client_existence'],
            'prefix'     => 'client',
            'as'         => 'client.',
        ], function () {
            Route::get('contracts',                       [\App\Http\Controllers\ClientPortal\ContractController::class, 'index'])->name('contracts.index');
            Route::get('contracts/{contract_token}',     [\App\Http\Controllers\ClientPortal\ContractController::class, 'show'])->name('contracts.show');
            Route::post('contracts/{contract_token}/sign', [\App\Http\Controllers\ClientPortal\ContractController::class, 'sign'])->name('contracts.sign');
            Route::get('contracts/{contract_token}/download', [\App\Http\Controllers\ClientPortal\ContractController::class, 'download'])->name('contracts.download');
        });
    }

    private function registerPublicRoutes(): void
    {
        // Public signing page — secured only by opaque token
        Route::get('/contract/sign/{token}',    [\App\Http\Controllers\ContractSigningController::class, 'show'])->name('contract.sign')->middleware('throttle:60,1');
        Route::post('/contract/sign/{token}',   [\App\Http\Controllers\ContractSigningController::class, 'sign'])->name('contract.sign.submit')->middleware('throttle:10,1');
        Route::get('/contract/signed/{token}',  [\App\Http\Controllers\ContractSigningController::class, 'signed'])->name('contract.signed');
        Route::post('/contract/decline/{token}',[\App\Http\Controllers\ContractSigningController::class, 'decline'])->name('contract.decline')->middleware('throttle:5,1');
        Route::get('/contract/declined/{token}',[\App\Http\Controllers\ContractSigningController::class, 'declined'])->name('contract.declined');
        Route::get('/contract/download/{token}',[\App\Http\Controllers\ContractSigningController::class, 'download'])->name('contract.download')->middleware('throttle:30,1');

        // Per-signer URL (individual token per signer for multi-signer)
        Route::get('/contract/s/{signerToken}', [\App\Http\Controllers\ContractSigningController::class, 'signerShow'])->name('contract.signer.sign')->middleware('throttle:60,1');
    }
}
