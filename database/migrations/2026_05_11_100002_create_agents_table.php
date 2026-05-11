<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('external_agent_id', 50)->unique()->comment('ID from Main Site');
            $table->string('business_name');
            $table->string('license_number', 100)->unique();
            $table->string('country', 100);
            $table->string('city', 100)->nullable();
            $table->enum('current_tier', ['bronze', 'silver', 'gold', 'diamond'])->default('bronze');
            $table->timestamp('tier_valid_until')->nullable();
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('pending_amount', 10, 2)->default(0)->comment('Fraction carry-over for amount_based');
            $table->text('internal_notes')->nullable()->comment('Admin only — hidden from agent');
            $table->timestamps();

            $table->index('external_agent_id');
            $table->index('current_tier');
            $table->index('tier_valid_until');
            $table->index('account_manager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
