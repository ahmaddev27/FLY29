<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notifications and messages now live in Firestore (real-time).
     * Server-side delivery preferences still live in MySQL
     * (user_notification_preferences) — only the inbox/feed tables are dropped.
     */
    public function up(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notifications');
    }

    public function down(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 100);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->foreignId('parent_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['receiver_id', 'is_read']);
            $table->index('sender_id');
            $table->index('created_at');
        });
    }
};
