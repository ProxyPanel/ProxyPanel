<?php

/** @var Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make(Str::random()),
        'port' => Helpers::getPort(),
        'passwd' => Str::random(),
        'vmess_id' => $faker->uuid,
        'method' => Helpers::getDefaultMethod(),
        'protocol' => Helpers::getDefaultProtocol(),
        'obfs' => Helpers::getDefaultObfs(),
        'transfer_enable' => (int) sysConfig('default_traffic') * MB,
        'expired_at' => date('Y-m-d', strtotime(sysConfig('default_days').' days')),
        'user_group_id' => null,
    ];
});
