<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    private array $countries = [
        'au' => '澳大利亚',
        'br' => '巴西',
        'ca' => '加拿大',
        'ch' => '瑞士',
        'cn' => '中国',
        'de' => '德国',
        'dk' => '丹麦',
        'eg' => '埃及',
        'fr' => '法国',
        'gr' => '希腊',
        'hk' => '香港',
        'id' => '印度尼西亚',
        'ie' => '爱尔兰',
        'il' => '以色列',
        'in' => '印度',
        'iq' => '伊拉克',
        'ir' => '伊朗',
        'it' => '意大利',
        'jp' => '日本',
        'kr' => '韩国',
        'mx' => '墨西哥',
        'my' => '马来西亚',
        'nl' => '荷兰',
        'no' => '挪威',
        'nz' => '纽西兰',
        'ph' => '菲律宾',
        'ru' => '俄罗斯',
        'se' => '瑞典',
        'sg' => '新加坡',
        'th' => '泰国',
        'tr' => '土耳其',
        'tw' => '台湾',
        'gb' => '英国',
        'us' => '美国',
        'vn' => '越南',
        'pl' => '波兰',
        'kz' => '哈萨克斯坦',
        'ua' => '乌克兰',
        'ro' => '罗马尼亚',
        'ae' => '阿联酋',
        'za' => '南非',
        'mm' => '缅甸',
        'is' => '冰岛',
        'fi' => '芬兰',
        'lu' => '卢森堡',
        'be' => '比利时',
        'bg' => '保加利亚',
        'lt' => '立陶宛',
        'co' => '哥伦比亚',
        'mo' => '澳门',
        'ke' => '肯尼亚',
        'cz' => '捷克',
        'md' => '摩尔多瓦',
        'es' => '西班牙',
        'pk' => '巴基斯坦',
        'pt' => '葡萄牙',
        'hu' => '匈牙利',
        'ar' => '阿根廷',
    ];

    public function run(): void
    {
        foreach ($this->countries as $code => $name) {
            Country::insert(['code' => $code, 'name' => $name]);
        }
    }
}
