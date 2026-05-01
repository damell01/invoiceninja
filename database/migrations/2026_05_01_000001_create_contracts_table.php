<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('contract_number', 100)->nullable();
            $table->string('title', 500);
            $table->string('status', 50)->default('draft');
            $table->longText('contract_body')->nullable();
            $table->string('public_token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('signed_pdf_path')->nullable();
            $table->string('contract_hash', 64)->nullable();
            $table->text('notes')->nullable();
            $table->text('footer')->nullable();
            $table->boolean('requires_counter_signature')->default(false);
            $table->timestamp('counter_signed_at')->nullable();
            $table->unsignedBigInteger('counter_signed_by')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'client_id']);
            $table->index(['company_id', 'deleted_at']);
            $table->index('public_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
