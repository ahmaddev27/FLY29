<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_levels', function (Blueprint $table) {
            $table->id();
            $table->enum('tier_name', ['bronze', 'silver', 'gold', 'diamond'])->unique();
            $table->unsignedInteger('min_packages_monthly')->comment('Threshold for this tier');
            $table->unsignedInteger('points_per_package')->comment('For package_based mode');
            $table->decimal('amount_per_point', 10, 2)->comment('USD per point for amount_based mode');
            $table->json('benefits')->nullable()->comment('Tier benefits descriptor');
            $table->unsignedInteger('display_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_levels');
    }
};
