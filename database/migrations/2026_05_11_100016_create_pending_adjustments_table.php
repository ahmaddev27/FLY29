<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['cash', 'package']);
            $table->integer('points_delta')->comment('Positive: add, Negative: subtract');
            $table->text('reason');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_adjustments');
    }
};
