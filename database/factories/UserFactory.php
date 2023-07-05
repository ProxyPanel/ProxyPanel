<?php

namespace Database\Factories;

use Helpers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->name(),
            'username' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'port' => Helpers::getPort(),
            'passwd' => Str::random(),
            'vmess_id' => fake()->uuid,
            'method' => Helpers::getDefaultMethod(),
            'protocol' => Helpers::getDefaultProtocol(),
            'obfs' => Helpers::getDefaultObfs(),
            'transfer_enable' => (int) sysConfig('default_traffic') * MiB,
            'expired_at' => date('Y-m-d', strtotime(sysConfig('default_days').' days')),
            'user_group_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
