<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redemption_requests', function (Blueprint $table) {
            // Who marked it as fulfilled
            $table->foreignId('fulfilled_by')->nullable()->after('fulfilled_at')
                  ->constrained('users')->nullOnDelete();

            // External reference for the actual disbursement / booking
            // (bank transfer reference, package booking confirmation, etc.)
            $table->string('fulfillment_reference', 150)->nullable()->after('fulfilled_by')
                  ->comment('Bank transfer ref or trip booking confirmation number');

            // Free-text note about how the fulfillment happened
            $table->text('fulfillment_notes')->nullable()->after('fulfillment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('redemption_requests', function (Blueprint $table) {
            $table->dropForeign(['fulfilled_by']);
            $table->dropColumn(['fulfilled_by', 'fulfillment_reference', 'fulfillment_notes']);
        });
    }
};
