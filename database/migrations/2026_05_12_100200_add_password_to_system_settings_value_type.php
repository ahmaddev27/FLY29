<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'password' as a value_type for SMTP password (and any future
     * masked-in-UI / encrypted-at-rest setting).
     *
     * MySQL: enum modification via raw SQL.
     * SQLite (tests): rebuild the column as a plain string — SQLite's
     * check constraints can't be modified in place.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE system_settings MODIFY COLUMN value_type ENUM('string','int','float','bool','json','password') NOT NULL DEFAULT 'string'");
            return;
        }

        // SQLite path: swap the column for an unconstrained string.
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('value_type_new', 20)->default('string')->after('value_type');
        });
        DB::statement('UPDATE system_settings SET value_type_new = value_type');
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn('value_type');
        });
        Schema::table('system_settings', function (Blueprint $table) {
            $table->renameColumn('value_type_new', 'value_type');
        });
    }

    public function down(): void
    {
        // Revert is a no-op on SQLite (tests); on MySQL restore the old enum.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE system_settings MODIFY COLUMN value_type ENUM('string','int','float','bool','json') NOT NULL DEFAULT 'string'");
        }
    }
};
