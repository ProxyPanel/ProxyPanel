<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make(Str::random()),
        'passwd' => Str::random(),
        'vmess_id' => $faker->uuid,
    ];
});
