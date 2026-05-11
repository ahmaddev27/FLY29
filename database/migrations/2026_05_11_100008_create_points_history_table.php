<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['cash', 'package']);
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('redemption_id')->nullable()->constrained('redemption_requests')->nullOnDelete();
            $table->integer('points_delta')->comment('Positive: credit, Negative: debit');
            $table->integer('balance_after')->comment('Snapshot of available_points after this operation');
            $table->enum('source', [
                'transaction',
                'redemption',
                'manual_adjustment',
                'rejection_refund',
                'cancellation_refund',
                'reversal',
            ]);
            $table->text('description')->nullable();
            $table->json('config_snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Admin/AM user id if manual');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['agent_id', 'wallet_type']);
            $table->index('created_at');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_history');
    }
};
