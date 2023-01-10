<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (User::doesntExist()) {
            $this->call(PresetSeeder::class);
        }

        if (Article::doesntExist()) {
            $this->call(ArticleSeeder::class);
        }
    }
}
