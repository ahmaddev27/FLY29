<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value');
            $table->enum('value_type', ['string', 'int', 'float', 'bool', 'json'])->default('string');
            $table->string('category', 50)->default('general')->comment('grouping in admin UI');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->comment('whether agents can see this setting');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
