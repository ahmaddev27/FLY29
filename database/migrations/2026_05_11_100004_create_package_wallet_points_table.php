<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_wallet_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('available_points')->default(0);
            $table->integer('locked_points')->default(0);
            $table->bigInteger('lifetime_earned')->default(0);
            $table->bigInteger('lifetime_redeemed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_wallet_points');
    }
};
