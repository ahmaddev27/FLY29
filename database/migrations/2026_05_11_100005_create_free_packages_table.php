<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('destination', 100);
            $table->unsignedInteger('points_required');
            $table->unsignedInteger('duration_days')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_packages');
    }
};
