<?php

namespace Database\Seeders;

use App\Models\SsConfig;
use Illuminate\Database\Seeder;

class SsConfigSeeder extends Seeder
{
    private array $methodList = [
        'none',
        'rc4-md5',
        'aes-128-cfb',
        'aes-192-cfb',
        'aes-256-cfb',
        'aes-128-ctr',
        'aes-192-ctr',
        'aes-256-ctr',
        'camellia-128-cfb',
        'camellia-192-cfb',
        'camellia-256-cfb',
        'salsa20',
        'chacha20',
        'chacha20-ietf',
        'chacha20-ietf-poly1305',
        'chacha20-poly1305',
        'xchacha-ietf-poly1305',
        'aes-128-gcm',
        'aes-192-gcm',
        'aes-256-gcm',
        'sodium-aes-256-gcm',
    ];

    private array $protocolList = [
        'origin',
        'auth_sha1_v4',
        'auth_aes128_md5',
        'auth_aes128_sha1',
        'auth_chain_a',
        'auth_chain_b',
        'auth_chain_c',
        'auth_chain_d',
        'auth_chain_e',
        'auth_chain_f',
    ];

    private array $obfsList = [
        'plain',
        'http_simple',
        'http_post',
        'tls1.2_ticket_auth',
        'tls1.2_ticket_fastauth',
    ];

    public function run(): void
    {
        foreach ($this->methodList as $i => $method) {
            SsConfig::insert(['name' => $method, 'type' => 1]);
            if ($i === 0) {
                SsConfig::type(1)->whereName($method)->first()->setDefault();
            }
        }
        foreach ($this->protocolList as $i => $method) {
            SsConfig::insert(['name' => $method, 'type' => 2]);
            if ($i === 0) {
                SsConfig::type(2)->whereName($method)->first()->setDefault();
            }
        }
        foreach ($this->obfsList as $i => $obs) {
            SsConfig::insert(['name' => $obs, 'type' => 3]);
            if ($i === 0) {
                SsConfig::type(3)->whereName($obs)->first()->setDefault();
            }
        }
    }
}
