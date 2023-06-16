<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {// 生成最初的等级
        Level::insert(['level' => 0, 'name' => 'Free']);
        for ($i = 1; $i < 8; $i++) {
            Level::insert(['level' => $i, 'name' => 'VIP-'.$i]);
        }
    }
}
