<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('from_tier', ['bronze', 'silver', 'gold', 'diamond'])->nullable();
            $table->enum('to_tier', ['bronze', 'silver', 'gold', 'diamond']);
            $table->enum('action', ['upgrade', 'downgrade', 'manual', 'initial', 'renewal']);
            $table->unsignedInteger('packages_at_time')->default(0);
            $table->timestamp('valid_until');
            $table->enum('triggered_by', ['system', 'admin'])->default('system');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('agent_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_history');
    }
};
