<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::doesntExist()) {
            $this->call(PresetSeeder::class);
        }

        if (Article::doesntExist()) {
            $this->call(ArticleSeeder::class);
        }
    }
}
