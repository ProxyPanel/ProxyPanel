<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    private array $labels = [
        'Netflix',
        'Hulu',
        'HBO',
        'Amazon Video',
        'DisneyNow',
        'BBC',
        'Channel 4',
        'Fox+',
        'Happyon',
        'AbemeTV',
        'DMM',
        'NicoNico',
        'Pixiv',
        'TVer',
        'TVB',
        'HBO Go',
        'BiliBili港澳台',
        '動畫瘋',
        '四季線上影視',
        'LINE TV',
        'Youtube Premium',
        '中国视频网站',
        '网易云音乐',
        'QQ音乐',
        'DisneyPlus',
        'Pandora',
        'SoundCloud',
        'Spotify',
        'TIDAL',
        'TikTok',
        'Pornhub',
        'Twitch',
    ];

    public function run(): void
    { // 节点用标签
        foreach ($this->labels as $label) {
            Label::insert(['name' => $label]);
        }
    }
}
