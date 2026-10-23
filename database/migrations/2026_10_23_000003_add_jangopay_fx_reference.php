<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jangopay_attempts', 'fx_reference')) {
            Schema::table('jangopay_attempts', function (Blueprint $table) {
                $table->text('fx_reference')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Retain the approved pricing reference with the financial evidence.
    }
};
