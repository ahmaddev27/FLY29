<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('variant', ['info', 'success', 'warning', 'danger'])->default('info');

            // Audience filter (empty arrays = unrestricted)
            $table->json('tier_filter')->nullable()->comment('e.g. ["gold","diamond"] — null = all tiers');
            $table->json('country_filter')->nullable()->comment('e.g. ["SA","AE"] — null = all countries');

            $table->boolean('send_email')->default(false)->comment('also dispatch email at create time');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable()->comment('after this time, banner is hidden');

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['announcement_id', 'agent_id']);
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
    }
};
