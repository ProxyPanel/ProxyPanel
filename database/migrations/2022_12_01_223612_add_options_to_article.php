<?php

use App\Models\Article;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionsToArticle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article', function (Blueprint $table) {
            $table->char('language', 5)->comment('语言')->default(config('app.locale'))->after('title');
            $table->string('category')->comment('分组名')->nullable()->after('language');
            $table->dropColumn('summary');
        });

        foreach (Article::all() as $article) {
            $article->update(['language' => config('app.locale')]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('article', function (Blueprint $table) {
            $table->string('summary')->nullable()->comment('简介')->after('title');
            $table->dropColumn('language', 'category');
        });
    }
}
