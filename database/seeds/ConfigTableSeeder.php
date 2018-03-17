<?php

use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::insert("INSERT INTO `config` VALUES ('1', 'is_rand_port', 0);");
        DB::insert("INSERT INTO `config` VALUES ('2', 'is_user_rand_port', 0);");
        DB::insert("INSERT INTO `config` VALUES ('3', 'invite_num', 3);");
        DB::insert("INSERT INTO `config` VALUES ('4', 'is_register', 1);");
        DB::insert("INSERT INTO `config` VALUES ('5', 'is_invite_register', 1);");
        DB::insert("INSERT INTO `config` VALUES ('6', 'website_name', 'SSRPanel');");
        DB::insert("INSERT INTO `config` VALUES ('7', 'is_reset_password', 1);");
        DB::insert("INSERT INTO `config` VALUES ('8', 'reset_password_times', 3);");
        DB::insert("INSERT INTO `config` VALUES ('9', 'website_url', 'http://www.ssrpanel.com');");
        DB::insert("INSERT INTO `config` VALUES ('10', 'is_active_register', 1);");
        DB::insert("INSERT INTO `config` VALUES ('11', 'active_times', 3);");
        DB::insert("INSERT INTO `config` VALUES ('12', 'login_add_score', 1);");
        DB::insert("INSERT INTO `config` VALUES ('13', 'min_rand_score', 1);");
        DB::insert("INSERT INTO `config` VALUES ('14', 'max_rand_score', 100);");
        DB::insert("INSERT INTO `config` VALUES ('15', 'wechat_qrcode', '');");
        DB::insert("INSERT INTO `config` VALUES ('16', 'alipay_qrcode', '');");
        DB::insert("INSERT INTO `config` VALUES ('17', 'login_add_score_range', 1440);");
        DB::insert("INSERT INTO `config` VALUES ('18', 'referral_traffic', 1024);");
        DB::insert("INSERT INTO `config` VALUES ('19', 'referral_percent', 0.2);");
        DB::insert("INSERT INTO `config` VALUES ('20', 'referral_money', 100);");
        DB::insert("INSERT INTO `config` VALUES ('21', 'referral_status', 1);");
        DB::insert("INSERT INTO `config` VALUES ('22', 'default_traffic', 1024);");
        DB::insert("INSERT INTO `config` VALUES ('23', 'traffic_warning', 0);");
        DB::insert("INSERT INTO `config` VALUES ('24', 'traffic_warning_percent', 80);");
        DB::insert("INSERT INTO `config` VALUES ('25', 'expire_warning', 0);");
        DB::insert("INSERT INTO `config` VALUES ('26', 'expire_days', 15);");
        DB::insert("INSERT INTO `config` VALUES ('27', 'reset_traffic', 1);");
        DB::insert("INSERT INTO `config` VALUES ('28', 'default_days', 7);");
        DB::insert("INSERT INTO `config` VALUES ('29', 'subscribe_max', 3);");
        DB::insert("INSERT INTO `config` VALUES ('30', 'min_port', 10000);");
        DB::insert("INSERT INTO `config` VALUES ('31', 'max_port', 40000);");
        DB::insert("INSERT INTO `config` VALUES ('32', 'is_captcha', 0);");
        DB::insert("INSERT INTO `config` VALUES ('33', 'is_traffic_ban', 1);");
        DB::insert("INSERT INTO `config` VALUES ('34', 'traffic_ban_value', 10);");
        DB::insert("INSERT INTO `config` VALUES ('35', 'traffic_ban_time', 60);");
        DB::insert("INSERT INTO `config` VALUES ('36', 'is_clear_log', 1);");
        DB::insert("INSERT INTO `config` VALUES ('37', 'is_node_crash_warning', 0);");
        DB::insert("INSERT INTO `config` VALUES ('38', 'crash_warning_email', '');");
        DB::insert("INSERT INTO `config` VALUES ('39', 'is_server_chan', 0);");
        DB::insert("INSERT INTO `config` VALUES ('40', 'server_chan_key', '');");
        DB::insert("INSERT INTO `config` VALUES ('41', 'is_subscribe_ban', 1);");
        DB::insert("INSERT INTO `config` VALUES ('42', 'subscribe_ban_times', 20);");
        DB::insert("INSERT INTO `config` VALUES ('43', 'paypal_status', 0);");
        DB::insert("INSERT INTO `config` VALUES ('44', 'paypal_client_id', '');");
        DB::insert("INSERT INTO `config` VALUES ('45', 'paypal_client_secret', '');");
        DB::insert("INSERT INTO `config` VALUES ('46', 'is_free_code', 0);");
        DB::insert("INSERT INTO `config` VALUES ('47', 'is_forbid_robot', 0);");
        DB::insert("INSERT INTO `config` VALUES ('48', 'subscribe_domain', '');");
        DB::insert("INSERT INTO `config` VALUES ('49', 'auto_release_port', 1);");
        DB::insert("INSERT INTO `config` VALUES ('50', 'is_youzan', 0);");
        DB::insert("INSERT INTO `config` VALUES ('51', 'youzan_client_id', '');");
        DB::insert("INSERT INTO `config` VALUES ('52', 'youzan_client_secret', '');");
        DB::insert("INSERT INTO `config` VALUES ('53', 'kdt_id', '');");
    }
}
