<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemption_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cash', 'package']);
            $table->unsignedInteger('points');
            $table->decimal('cash_value_usd', 10, 2)->nullable()->comment('For cash redemptions');
            $table->foreignId('package_id')->nullable()->constrained('free_packages')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'fulfilled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('fulfilled')->default(false);
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['agent_id', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemption_requests');
    }
};
