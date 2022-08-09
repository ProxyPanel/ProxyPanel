<?php

use App\Models\Country;
use App\Models\Node;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyNodeForView extends Migration
{
    public function up(): void
    {
        Schema::table('node', function (Blueprint $table) {
            $table->tinyInteger('is_display')->default(3)->comment('节点显示模式：0-不显示、1-只页面、2-只订阅、3-都可')->after('traffic_rate');
        });

        foreach (Node::all() as $node) {
            $node->is_display = $node->is_subscribe ? 3 : 1;
            $node->save();
        }

        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('is_subscribe');
        });

        $country = Country::whereCode('uk')->first();
        if ($country) {
            $country->code = 'gb';
            $country->save();
        }
        Node::whereCountryCode('uk')->update(['country_code' => 'gb']);
    }

    public function down(): void
    {
        Schema::table('node', function (Blueprint $table) {
            $table->boolean('is_subscribe')->default(1)->index()->comment('是否允许用户订阅该节点：0-否、1-是');
        });

        foreach (Node::all() as $node) {
            $node->is_subscribe = $node->is_display ? 1 : 0;
            $node->save();
        }

        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('is_display');
        });

        $country = Country::whereCode('gb')->first();
        if ($country) {
            $country->code = 'uk';
            $country->save();
        }
        Node::whereCountryCode('gb')->update(['country_code' => 'uk']);
    }
}
