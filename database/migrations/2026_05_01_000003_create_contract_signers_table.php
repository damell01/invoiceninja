<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_signers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('client_contact_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->unsignedSmallInteger('signing_order')->default(1);
            $table->boolean('is_internal')->default(false);
            $table->string('status', 50)->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->longText('signature_data')->nullable();
            $table->string('signature_type', 20)->nullable();
            $table->string('signed_name', 255)->nullable();
            $table->string('signed_title', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('decline_reason', 1000)->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'signing_order']);
            $table->index('token');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signers');
    }
};
