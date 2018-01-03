<?php

use Illuminate\Database\Seeder;

class SsConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::insert("INSERT INTO `ss_config` VALUES ('1', 'none', '1', '0', '0', '2017-08-01 13:12:23', '2017-08-01 13:12:23');");
        DB::insert("INSERT INTO `ss_config` VALUES ('2', 'rc4-md5', '1', '0', '0', '2017-08-01 13:12:29', '2017-08-01 13:12:29');");
        DB::insert("INSERT INTO `ss_config` VALUES ('3', 'bf-cfb', '1', '0', '0', '2017-08-01 13:13:05', '2017-08-01 13:13:05');");
        DB::insert("INSERT INTO `ss_config` VALUES ('4', 'aes-128-cfb', '1', '0', '0', '2017-08-01 13:13:13', '2017-08-01 13:13:13');");
        DB::insert("INSERT INTO `ss_config` VALUES ('5', 'aes-192-cfb', '1', '0', '0', '2017-08-01 13:13:25', '2017-08-01 13:13:25');");
        DB::insert("INSERT INTO `ss_config` VALUES ('6', 'aes-256-cfb', '1', '0', '0', '2017-08-01 13:13:39', '2017-08-01 13:13:39');");
        DB::insert("INSERT INTO `ss_config` VALUES ('7', 'aes-128-ctr', '1', '0', '0', '2017-08-01 13:13:46', '2017-08-01 13:13:46');");
        DB::insert("INSERT INTO `ss_config` VALUES ('8', 'aes-192-ctr', '1', '1', '0', '2017-08-01 13:13:53', '2017-08-01 13:13:53');");
        DB::insert("INSERT INTO `ss_config` VALUES ('9', 'aes-256-ctr', '1', '0', '0', '2017-08-01 13:14:00', '2017-08-01 13:14:00');");
        DB::insert("INSERT INTO `ss_config` VALUES ('10', 'camellia-128-cfb', '1', '0', '0', '2017-08-01 13:14:08', '2017-08-01 13:14:08');");
        DB::insert("INSERT INTO `ss_config` VALUES ('11', 'camellia-192-cfb', '1', '0', '0', '2017-08-01 13:14:12', '2017-08-01 13:14:12');");
        DB::insert("INSERT INTO `ss_config` VALUES ('12', 'camellia-256-cfb', '1', '0', '0', '2017-08-01 13:14:51', '2017-08-01 13:14:51');");
        DB::insert("INSERT INTO `ss_config` VALUES ('13', 'salsa20', '1', '0', '0', '2017-08-01 13:15:09', '2017-08-01 13:15:09');");
        DB::insert("INSERT INTO `ss_config` VALUES ('14', 'chacha20', '1', '0', '0', '2017-08-01 13:15:16', '2017-08-01 13:15:16');");
        DB::insert("INSERT INTO `ss_config` VALUES ('15', 'chacha20-ietf', '1', '0', '0', '2017-08-01 13:15:27', '2017-08-01 13:15:27');");
        DB::insert("INSERT INTO `ss_config` VALUES ('16', 'chacha20-ietf-poly1305', '1', '0', '0', '2017-08-01 13:15:39', '2017-08-01 13:15:39');");
        DB::insert("INSERT INTO `ss_config` VALUES ('17', 'chacha20-poly1305', '1', '0', '0', '2017-08-01 13:15:46', '2017-08-01 13:15:46');");
        DB::insert("INSERT INTO `ss_config` VALUES ('18', 'xchacha-ietf-poly1305', '1', '0', '0', '2017-08-01 13:21:51', '2017-08-01 13:21:51');");
        DB::insert("INSERT INTO `ss_config` VALUES ('19', 'aes-128-gcm', '1', '0', '0', '2017-08-01 13:22:05', '2017-08-01 13:22:05');");
        DB::insert("INSERT INTO `ss_config` VALUES ('20', 'aes-192-gcm', '1', '0', '0', '2017-08-01 13:22:12', '2017-08-01 13:22:12');");
        DB::insert("INSERT INTO `ss_config` VALUES ('21', 'aes-256-gcm', '1', '0', '0', '2017-08-01 13:22:19', '2017-08-01 13:22:19');");
        DB::insert("INSERT INTO `ss_config` VALUES ('22', 'sodium-aes-256-gcm', '1', '0', '0', '2017-08-01 13:22:32', '2017-08-01 13:22:32');");
        DB::insert("INSERT INTO `ss_config` VALUES ('23', 'origin', '2', '0', '0', '2017-08-01 13:23:57', '2017-08-01 13:23:57');");
        DB::insert("INSERT INTO `ss_config` VALUES ('24', 'auth_sha1_v4', '2', '0', '0', '2017-08-01 13:24:41', '2017-08-01 13:24:41');");
        DB::insert("INSERT INTO `ss_config` VALUES ('25', 'auth_aes128_md5', '2', '0', '0', '2017-08-01 13:24:58', '2017-08-01 13:24:58');");
        DB::insert("INSERT INTO `ss_config` VALUES ('26', 'auth_aes128_sha1', '2', '0', '0', '2017-08-01 13:25:11', '2017-08-01 13:25:11');");
        DB::insert("INSERT INTO `ss_config` VALUES ('27', 'auth_chain_a', '2', '1', '0', '2017-08-01 13:25:24', '2017-08-01 13:25:24');");
        DB::insert("INSERT INTO `ss_config` VALUES ('28', 'auth_chain_b', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');");
        DB::insert("INSERT INTO `ss_config` VALUES ('29', 'plain', '3', '0', '0', '2017-08-01 13:29:14', '2017-08-01 13:29:14');");
        DB::insert("INSERT INTO `ss_config` VALUES ('30', 'http_simple', '3', '0', '0', '2017-08-01 13:29:30', '2017-08-01 13:29:30');");
        DB::insert("INSERT INTO `ss_config` VALUES ('31', 'http_post', '3', '0', '0', '2017-08-01 13:29:38', '2017-08-01 13:29:38');");
        DB::insert("INSERT INTO `ss_config` VALUES ('32', 'tls1.2_ticket_auth', '3', '1', '0', '2017-08-01 13:29:51', '2017-08-01 13:29:51');");
        DB::insert("INSERT INTO `ss_config` VALUES ('33', 'tls1.2_ticket_fastauth', '3', '0', '0', '2017-08-01 14:02:19', '2017-08-01 14:02:19');");
        DB::insert("INSERT INTO `ss_config` VALUES ('34', 'auth_chain_c', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');");
        DB::insert("INSERT INTO `ss_config` VALUES ('35', 'auth_chain_d', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');");
        DB::insert("INSERT INTO `ss_config` VALUES ('36', 'auth_chain_e', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');");
        DB::insert("INSERT INTO `ss_config` VALUES ('37', 'auth_chain_f', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');");
    }
}
