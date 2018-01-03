<?php

use Illuminate\Database\Seeder;

class LevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::insert("INSERT INTO `level` VALUES (1, '1', '倔强青铜', '2017-10-26 15:56:52', '2017-10-26 15:38:58');");
        DB::insert("INSERT INTO `level` VALUES (2, '2', '秩序白银', '2017-10-26 15:57:30', '2017-10-26 12:37:51');");
        DB::insert("INSERT INTO `level` VALUES (3, '3', '荣耀黄金', '2017-10-26 15:41:31', '2017-10-26 15:41:31');");
        DB::insert("INSERT INTO `level` VALUES (4, '4', '尊贵铂金', '2017-10-26 15:41:38', '2017-10-26 15:41:38');");
        DB::insert("INSERT INTO `level` VALUES (5, '5', '永恒钻石', '2017-10-26 15:41:47', '2017-10-26 15:41:47');");
        DB::insert("INSERT INTO `level` VALUES (6, '6', '至尊黑曜', '2017-10-26 15:41:56', '2017-10-26 15:41:56');");
        DB::insert("INSERT INTO `level` VALUES (7, '7', '最强王者', '2017-10-26 15:42:02', '2017-10-26 15:42:02');");
    }
}
