<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user')->insert([
            'id'                   => 1,
            'username'             => 'admin',
            'password'             => 'e10adc3949ba59abbe56e057f20f883e',
            'port'                 => 10000,
            'passwd'               => '@123',
            'transfer_enable'      => 1073741824000,
            'u'                    => 0,
            'd'                    => 0,
            't'                    => 0,
            'enable'               => 1,
            'method'               => 'aes-192-ctr',
            'protocol'             => 'auth_chain_a',
            'protocol_param'       => '',
            'obfs'                 => 'tls1.2_ticket_auth',
            'obfs_param'           => '',
            'speed_limit_per_con'  => 204800,
            'speed_limit_per_user' => 204800,
            'gender'               => 1,
            'wechat'               => '',
            'qq'                   => '',
            'usage'                => 1,
            'pay_way'              => 3,
            'balance'              => '0.00',
            'score'                => 0,
            'enable_time'          => date('Y-m-d'),
            'expire_time'          => '2099-01-01',
            'ban_time'             => 0,
            'remark'               => '',
            'level'                => 1,
            'is_admin'             => 1,
            'reg_ip'               => '127.0.0.1',
            'last_login'           => 0,
            'referral_uid'         => 0,
            'traffic_reset_day'    => 0,
            'status'               => 0,
            'remember_token'       => ''
        ]);
    }
}
