<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->unsignedSmallInteger('response_code');
            $table->json('response_body')->nullable();
            $table->string('api_key_used', 50)->nullable()->comment('Masked or hashed identifier');
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('reference_id', 100)->nullable();
            $table->enum('status', ['success', 'duplicate_ignored', 'unauthorized', 'failed', 'rate_limited'])->default('success');
            $table->timestamp('created_at')->useCurrent();

            $table->index('endpoint');
            $table->index('reference_id');
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
