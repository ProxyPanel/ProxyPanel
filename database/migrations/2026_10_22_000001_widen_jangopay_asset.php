<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Currency/network pairs use at most 80 ASCII characters, including the colon.
        // Keep this separate so databases that already ran the first migration are upgraded too.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE jangopay_attempts MODIFY selected_asset VARCHAR(80) NULL');
        }
        // SQLite TEXT affinity already stores the full value irrespective of the declared width.
    }

    public function down(): void
    {
        // Do not truncate saved financial evidence on rollback.
    }
};
