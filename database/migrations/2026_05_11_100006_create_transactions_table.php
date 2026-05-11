<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->string('reference_id', 100)->unique()->comment('Idempotency key from Main Site');
            $table->enum('transaction_type', ['package', 'service']);
            $table->decimal('amount_usd', 10, 2);
            $table->string('destination', 100)->nullable();
            $table->unsignedInteger('points_awarded');
            $table->json('config_snapshot')->comment('Active settings at time of transaction');
            $table->timestamp('transaction_date')->comment('Original transaction timestamp from Main Site');
            $table->timestamps();

            $table->index(['agent_id', 'transaction_date']);
            $table->index('transaction_type');
            $table->index('created_at');
        });

        // Pending transactions (for suspended agents — held for later processing)
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('external_agent_id', 50);
            $table->string('reference_id', 100)->unique();
            $table->json('payload')->comment('Full webhook payload');
            $table->enum('reason', ['agent_suspended', 'agent_not_found', 'system_error']);
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('external_agent_id');
            $table->index('processed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_transactions');
        Schema::dropIfExists('transactions');
    }
};
