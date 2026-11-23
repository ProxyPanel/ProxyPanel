<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fresh installs seed configs after migrations; avoid pre-creating a duplicate.
        if (Config::exists()) {
            Config::firstOrCreate(['name' => 'jangopay_assets'], ['value' => null]);
        }
        if (! Schema::hasTable('jangopay_attempts')) {
            Schema::create('jangopay_attempts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('payment_id')->unique();
                $table->string('order_id', 64)->unique();
                $table->string('invoice_id', 120)->nullable()->unique();
                $table->string('txn_id', 120)->nullable()->unique();
                $table->string('tx_hash', 160)->nullable();
                $table->string('base_currency', 3);
                $table->string('base_amount', 32);
                $table->string('amount_usd', 32);
                $table->string('fx_rate', 40);
                $table->text('allowed_assets');
                $table->string('selected_asset', 20)->nullable();
                $table->string('quote_amount', 80)->nullable();
                $table->text('receive_address')->nullable();
                $table->string('state', 40)->index();
                $table->string('failure_code', 80)->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
                // No FK: ProxyPanel automatically purges old payment records.
                // Keep the reconciliation snapshot even when old payment data is pruned.
            });
        }
        if (! Schema::hasTable('jangopay_events')) {
            Schema::create('jangopay_events', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('event_hash', 64)->unique();
                $table->string('invoice_id', 120);
                $table->string('txn_id', 120);
                $table->string('tx_hash', 160)->nullable();
                $table->string('status', 40)->default('received');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Payment evidence is intentionally retained. Delete only after separately approved retention review.
    }
};
