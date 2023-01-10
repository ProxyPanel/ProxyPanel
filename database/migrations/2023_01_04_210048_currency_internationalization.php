<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

class CurrencyInternationalization extends Migration
{
    public function up()
    {
        Schema::dropIfExists('products_pool');
        Config::whereName('stripe_currency')->update(['name' => 'standard_currency', 'value' => strtoupper(Config::find('stripe_currency')->value ?? 'CNY')]);
    }

    public function down()
    {
        Config::whereName('standard_currency')->update(['name' => 'stripe_currency']);
    }
}
