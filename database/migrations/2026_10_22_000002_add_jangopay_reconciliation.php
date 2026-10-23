<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['last_checked_at', 'remote_invoice_status', 'remote_txn_status', 'review_note'] as $column) {
            if (! Schema::hasColumn('jangopay_attempts', $column)) {
                Schema::table('jangopay_attempts', function (Blueprint $t) use ($column) {
                    if ($column === 'last_checked_at') {
                        $t->timestamp($column)->nullable()->index();
                    } elseif ($column === 'review_note') {
                        $t->text($column)->nullable();
                    } else {
                        $t->string($column, 40)->nullable();
                    }
                });
            }
        }
        if (! Schema::hasTable('jangopay_actions')) {
            Schema::create('jangopay_actions', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('attempt_id')->index();
                $t->unsignedInteger('user_id')->nullable();
                $t->string('action', 40);
                $t->text('note')->nullable();
                $t->timestamp('created_at');
            });
        }
        if (Schema::hasTable('permissions') && DB::table('permissions')->exists()) {
            foreach (['index', 'check', 'resolve'] as $action) {
                DB::table('permissions')->insertOrIgnore(['name' => 'admin.jangopay.'.$action,
                    'guard_name' => 'web', 'description' => 'JangoPay reconciliation: '.$action]);
            }
            cache()->forget(config('permission.cache.key', 'spatie.permission.cache'));
        }
    }

    public function down(): void
    {
        // Retain payment evidence and operator actions.
    }
};
