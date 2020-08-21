# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.18)
# Database: 2
# Generation Time: 2017-07-29 06:28:10 +0000
# ************************************************************


/*!40101 SET @old_character_set_client = @@character_set_client */;
/*!40101 SET @old_character_set_results = @@character_set_results */;
/*!40101 SET @old_collation_connection = @@collation_connection */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @old_foreign_key_checks = @@foreign_key_checks, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @old_sql_mode = @@sql_mode, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @old_sql_notes = @@sql_notes, SQL_NOTES = 0 */;


-- ----------------------------
-- Table structure for ss_node
-- ----------------------------
CREATE TABLE `ss_node`
(
    `id`             INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `type`           TINYINT(1) UNSIGNED  NOT NULL DEFAULT 1 COMMENT '服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet',
    `name`           VARCHAR(128)         NOT NULL COMMENT '名称',
    `country_code`   CHAR(5)              NOT NULL DEFAULT 'un' COMMENT '国家代码',
    `server`         VARCHAR(255)                  DEFAULT NULL COMMENT '服务器域名地址',
    `ip`             CHAR(15)                      DEFAULT NULL COMMENT '服务器IPV4地址',
    `ipv6`           VARCHAR(45)                   DEFAULT NULL COMMENT '服务器IPV6地址',
    `level`          TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '等级：0-无等级，全部可见',
    `speed_limit`    BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '节点限速，为0表示不限速，单位Byte',
    `client_limit`   SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '设备数限制',
    `relay_server`   VARCHAR(255)                  DEFAULT NULL COMMENT '中转地址',
    `relay_port`     SMALLINT(5) UNSIGNED          DEFAULT 0 COMMENT '中转端口',
    `description`    VARCHAR(255)                  DEFAULT NULL COMMENT '节点简单描述',
    `geo`            VARCHAR(255)                  DEFAULT NULL COMMENT '节点地理位置',
    `method`         VARCHAR(32)          NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
    `protocol`       VARCHAR(64)          NOT NULL DEFAULT 'origin' COMMENT '协议',
    `protocol_param` VARCHAR(128)                  DEFAULT NULL COMMENT '协议参数',
    `obfs`           VARCHAR(64)          NOT NULL DEFAULT 'plain' COMMENT '混淆',
    `obfs_param`     VARCHAR(255)                  DEFAULT NULL COMMENT '混淆参数',
    `traffic_rate`   FLOAT(6, 2) UNSIGNED NOT NULL DEFAULT '1.00' COMMENT '流量比率',
    `is_subscribe`   BIT                  NOT NULL DEFAULT 1 COMMENT '是否允许用户订阅该节点：0-否、1-是',
    `is_ddns`        BIT                  NOT NULL DEFAULT 0 COMMENT '是否使用DDNS：0-否、1-是',
    `is_relay`       BIT                  NOT NULL DEFAULT 0 COMMENT '是否中转节点：0-否、1-是',
    `is_udp`         BIT                  NOT NULL DEFAULT 1 COMMENT '是否启用UDP：0-不启用、1-启用',
    `push_port`      SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '消息推送端口',
    `detection_type` TINYINT(1)           NOT NULL DEFAULT '1' COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部',
    `compatible`     BIT                  NOT NULL DEFAULT 0 COMMENT '兼容SS',
    `single`         BIT                  NOT NULL DEFAULT 0 COMMENT '启用单端口功能：0-否、1-是',
    `port`           SMALLINT(5) UNSIGNED          DEFAULT NULL COMMENT '单端口的端口号或连接端口号',
    `passwd`         VARCHAR(255)                  DEFAULT NULL COMMENT '单端口的连接密码',
    `sort`           TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠前显示',
    `status`         BIT                  NOT NULL DEFAULT 1 COMMENT '状态：0-维护、1-正常',
    `v2_alter_id`    SMALLINT(5) UNSIGNED NOT NULL DEFAULT '16' COMMENT 'V2Ray额外ID',
    `v2_port`        SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'V2Ray服务端口',
    `v2_method`      VARCHAR(32)          NOT NULL DEFAULT 'aes-128-gcm' COMMENT 'V2Ray加密方式',
    `v2_net`         VARCHAR(16)          NOT NULL DEFAULT 'tcp' COMMENT 'V2Ray传输协议',
    `v2_type`        VARCHAR(32)          NOT NULL DEFAULT 'none' COMMENT 'V2Ray伪装类型',
    `v2_host`        VARCHAR(255)         NOT NULL DEFAULT '' COMMENT 'V2Ray伪装的域名',
    `v2_path`        VARCHAR(255)         NOT NULL DEFAULT '' COMMENT 'V2Ray的WS/H2路径',
    `v2_tls`         BIT                  NOT NULL DEFAULT 0 COMMENT 'V2Ray连接TLS：0-未开启、1-开启',
    `tls_provider`   TEXT                          DEFAULT NULL COMMENT 'V2Ray节点的TLS提供商授权信息',
    `created_at`     DATETIME             NOT NULL COMMENT '创建时间',
    `updated_at`     DATETIME             NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    INDEX `idx_sub` (`is_subscribe`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点信息表';


-- ----------------------------
-- Table structure for ss_node_info
-- ----------------------------
CREATE TABLE `ss_node_info`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `uptime`   INT(10) UNSIGNED NOT NULL COMMENT '后端存活时长，单位秒',
    `load`     VARCHAR(255)     NOT NULL COMMENT '负载',
    `log_time` INT(10) UNSIGNED NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    INDEX `idx_node_id` (`node_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点心跳信息';


-- ----------------------------
-- Table structure for ss_node_online_log
-- ----------------------------
CREATE TABLE `ss_node_online_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`     INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    `online_user` INT(10) UNSIGNED NOT NULL COMMENT '在线用户数',
    `log_time`    INT(10) UNSIGNED NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    INDEX `idx_node_id` (`node_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点在线信息';


-- ----------------------------
-- Table structure for node_ping
-- ----------------------------
CREATE TABLE `node_ping`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '对应节点id',
    `ct`         INT(10)          NOT NULL DEFAULT '0' COMMENT '电信',
    `cu`         INT(10)          NOT NULL DEFAULT '0' COMMENT '联通',
    `cm`         INT(10)          NOT NULL DEFAULT '0' COMMENT '移动',
    `hk`         INT(10)          NOT NULL DEFAULT '0' COMMENT '香港',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_node_id` (`node_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点ping信息表';


-- ----------------------------
-- Table structure for node_label
-- ----------------------------
CREATE TABLE `node_label`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `label_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签ID',
    PRIMARY KEY (`id`),
    INDEX `idx_node_label` (`node_id`, `label_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点标签';


-- ----------------------------
-- Table structure for user
-- ----------------------------
CREATE TABLE `user`
(
    `id`              INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`        VARCHAR(64)          NOT NULL COMMENT '昵称',
    `email`           VARCHAR(128)         NOT NULL COMMENT '邮箱',
    `password`        VARCHAR(64)          NOT NULL COMMENT '密码',
    `port`            SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '代理端口',
    `passwd`          VARCHAR(16)          NOT NULL COMMENT '代理密码',
    `vmess_id`        CHAR(36)             NOT NULL,
    `transfer_enable` BIGINT(20) UNSIGNED  NOT NULL DEFAULT '1099511627776' COMMENT '可用流量，单位字节，默认1TiB',
    `u`               BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '已上传流量，单位字节',
    `d`               BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '已下载流量，单位字节',
    `t`               INT(10) UNSIGNED              DEFAULT NULL COMMENT '最后使用时间',
    `ip`              CHAR(15)                      DEFAULT NULL COMMENT '最后连接IP',
    `enable`          TINYINT(1)           NOT NULL DEFAULT 1 COMMENT '代理状态',
    `method`          VARCHAR(30)          NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
    `protocol`        VARCHAR(30)          NOT NULL DEFAULT 'origin' COMMENT '协议',
    `protocol_param`  VARCHAR(255)                  DEFAULT NULL COMMENT '协议参数',
    `obfs`            VARCHAR(30)          NOT NULL DEFAULT 'plain' COMMENT '混淆',
    `speed_limit`     BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '用户限速，为0表示不限速，单位Byte',
    `wechat`          VARCHAR(30)                   DEFAULT NULL COMMENT '微信',
    `qq`              VARCHAR(20)                   DEFAULT NULL COMMENT 'QQ',
    `credit`          INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '余额，单位分',
    `expired_at`      DATE                 NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
    `ban_time`        INT(10) UNSIGNED              DEFAULT NULL COMMENT '封禁到期时间',
    `remark`          TEXT COMMENT '备注',
    `level`           TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '等级，默认0级',
    `group_id`        INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '所属分组',
    `is_admin`        BIT                  NOT NULL DEFAULT 0 COMMENT '是否管理员：0-否、1-是',
    `reg_ip`          CHAR(15)             NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
    `last_login`      INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '最后登录时间',
    `inviter_id`      INT(10) UNSIGNED              DEFAULT NULL COMMENT '邀请人',
    `reset_time`      DATE                          DEFAULT NULL COMMENT '流量重置日期',
    `invite_num`      INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '可生成邀请码数',
    `status`          TINYINT(1)           NOT NULL DEFAULT '0' COMMENT '状态：-1-禁用、0-未激活、1-正常',
    `remember_token`  VARCHAR(255)                  DEFAULT NULL,
    `created_at`      DATETIME             NOT NULL COMMENT '创建时间',
    `updated_at`      DATETIME             NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unq_email` (`email`),
    INDEX `idx_search` (`enable`, `status`, `port`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户表';

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user`
    DISABLE KEYS */;
INSERT INTO `user`(`id`, `username`, `email`, `password`, `port`, `passwd`, `vmess_id`, `transfer_enable`, `u`, `d`, `t`, `enable`, `method`, `protocol`, `obfs`, `wechat`, `qq`, `credit`, `expired_at`, `remark`, `is_admin`, `reg_ip`, `status`, `created_at`, `updated_at`)
VALUES (1, '管理员', 'test@test.com', '$2y$10$ryMdx5ejvCSdjvZVZAPpOuxHrsAUY8FEINUATy6RCck6j9EeHhPfq', 10000, '@123', 'c6effafd-6046-7a84-376e-b0429751c304', 1099511627776, 0, 0, 0, 1, 'aes-256-cfb', 'origin', 'plain', '', '', 0.00, '2099-01-01', NULL, 1, '127.0.0.1', 1, Now(), Now());

/*!40000 ALTER TABLE `user`
    ENABLE KEYS */;
UNLOCK TABLES;


-- ----------------------------
-- Records of `user_group`
-- ----------------------------
CREATE TABLE `user_group`
(
    `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(255)     NOT NULL COMMENT '分组名称',
    `nodes` JSON DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户分组控制表';


-- ----------------------------
-- Table structure for `level`
-- ----------------------------
CREATE TABLE `level`
(
    `id`    INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '等级',
    `name`  VARCHAR(100)        NOT NULL COMMENT '等级名称',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='等级表';


-- ----------------------------
-- Records of `level`
-- ----------------------------
INSERT INTO `level`(`id`, `level`, `name`)
VALUES (1, '0', 'Free'),
       (2, '1', 'VIP1'),
       (3, '2', 'VIP2'),
       (4, '3', 'VIP3'),
       (5, '4', 'VIP4'),
       (6, '5', 'VIP5'),
       (7, '6', 'VIP6'),
       (8, '7', 'VIP7');


-- ----------------------------
-- Table structure for user_traffic_log
-- ----------------------------
CREATE TABLE `user_traffic_log`
(
    `id`       INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`  INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`  INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '节点ID',
    `u`        INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`        INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '下载流量',
    `rate`     FLOAT(6, 2) UNSIGNED NOT NULL COMMENT '倍率',
    `traffic`  VARCHAR(32)          NOT NULL COMMENT '产生流量',
    `log_time` INT(10) UNSIGNED     NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_node_time` (`user_id`, `node_id`, `log_time`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户流量日志';


-- ----------------------------
-- Table structure for ss_config
-- ----------------------------
CREATE TABLE `ss_config`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)         NOT NULL DEFAULT '' COMMENT '配置名',
    `type`       TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '类型：1-加密方式、2-协议、3-混淆',
    `is_default` BIT                 NOT NULL DEFAULT 0 COMMENT '是否默认：0-不是、1-是',
    `sort`       TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序：值越大排越前',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='通用配置';


-- ----------------------------
-- Records of ss_config
-- ----------------------------
INSERT INTO `ss_config`(`id`, `name`, `type`, `is_default`, `sort`)
VALUES ('1', 'none', '1', 0, '0'),
       ('2', 'rc4', '1', 0, '0'),
       ('3', 'rc4-md5', '1', 0, '0'),
       ('4', 'rc4-md5-6', '1', 0, '0'),
       ('5', 'bf-cfb', '1', 0, '0'),
       ('6', 'aes-128-cfb', '1', 0, '0'),
       ('7', 'aes-192-cfb', '1', 0, '0'),
       ('8', 'aes-256-cfb', '1', 1, '0'),
       ('9', 'aes-128-ctr', '1', 0, '0'),
       ('10', 'aes-192-ctr', '1', 0, '0'),
       ('11', 'aes-256-ctr', '1', 0, '0'),
       ('12', 'camellia-128-cfb', '1', 0, '0'),
       ('13', 'camellia-192-cfb', '1', 0, '0'),
       ('14', 'camellia-256-cfb', '1', 0, '0'),
       ('15', 'salsa20', '1', 0, '0'),
       ('16', 'xsalsa20', '1', 0, '0'),
       ('17', 'chacha20', '1', 0, '0'),
       ('18', 'xchacha20', '1', 0, '0'),
       ('19', 'chacha20-ietf', '1', 0, '0'),
       ('20', 'chacha20-ietf-poly1305', '1', 0, '0'),
       ('21', 'chacha20-poly1305', '1', 0, '0'),
       ('22', 'xchacha-ietf-poly1305', '1', 0, '0'),
       ('23', 'aes-128-gcm', '1', 0, '0'),
       ('24', 'aes-192-gcm', '1', 0, '0'),
       ('25', 'aes-256-gcm', '1', 0, '0'),
       ('26', 'sodium-aes-256-gcm', '1', 0, '0'),
       ('27', 'origin', '2', 1, '0'),
       ('28', 'auth_sha1_v4', '2', 0, '0'),
       ('29', 'auth_aes128_md5', '2', 0, '0'),
       ('30', 'auth_aes128_sha1', '2', 0, '0'),
       ('31', 'auth_chain_a', '2', 0, '0'),
       ('32', 'auth_chain_b', '2', 0, '0'),
       ('33', 'plain', '3', 1, '0'),
       ('34', 'http_simple', '3', 0, '0'),
       ('35', 'http_post', '3', 0, '0'),
       ('36', 'tls1.2_ticket_auth', '3', 0, '0'),
       ('37', 'tls1.2_ticket_fastauth', '3', 0, '0'),
       ('38', 'auth_chain_c', '2', 0, '0'),
       ('39', 'auth_chain_d', '2', 0, '0'),
       ('40', 'auth_chain_e', '2', 0, '0'),
       ('41', 'auth_chain_f', '2', 0, '0');


-- ----------------------------
-- Table structure for config
-- ----------------------------
CREATE TABLE `config`
(
    `name`  VARCHAR(255) NOT NULL COMMENT '配置名',
    `value` TEXT         NULL COMMENT '配置值',
    PRIMARY KEY (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='系统配置';


-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config`(`name`, `value`)
VALUES ('is_rand_port', 0),
       ('is_user_rand_port', 0),
       ('invite_num', 3),
       ('is_register', 1),
       ('is_invite_register', 2),
       ('website_name', 'ProxyPanel'),
       ('is_reset_password', 1),
       ('reset_password_times', 3),
       ('website_url', 'https://demo.proxypanel.ml'),
       ('referral_type', 0),
       ('active_times', 3),
       ('is_checkin', 1),
       ('min_rand_traffic', 10),
       ('max_rand_traffic', 500),
       ('wechat_qrcode', ''),
       ('alipay_qrcode', ''),
       ('traffic_limit_time', 1440),
       ('referral_traffic', 1024),
       ('referral_percent', 0.2),
       ('referral_money', 100),
       ('referral_status', 1),
       ('default_traffic', 1024),
       ('traffic_warning', 0),
       ('traffic_warning_percent', 80),
       ('expire_warning', 0),
       ('expire_days', 15),
       ('reset_traffic', 1),
       ('default_days', 7),
       ('subscribe_max', 3),
       ('min_port', 10000),
       ('max_port', 20000),
       ('is_captcha', 0),
       ('is_traffic_ban', 1),
       ('traffic_ban_value', 10),
       ('traffic_ban_time', 60),
       ('is_clear_log', 1),
       ('is_node_offline', 0),
       ('webmaster_email', ''),
       ('is_notification', 0),
       ('server_chan_key', ''),
       ('is_subscribe_ban', 1),
       ('subscribe_ban_times', 20),
       ('codepay_url', ''),
       ('codepay_id', ''),
       ('codepay_key', ''),
       ('is_free_code', 0),
       ('is_forbid_robot', 0),
       ('subscribe_domain', ''),
       ('auto_release_port', 1),
       ('website_callback_url', ''),
       ('web_api_url', ''),
       ('v2ray_license', ''),
       ('trojan_license', ''),
       ('v2ray_tls_provider', ''),
       ('website_analytics', ''),
       ('website_customer_service', ''),
       ('register_ip_limit', 5),
       ('is_email_filtering', '0'),
       ('is_push_bear', 0),
       ('push_bear_send_key', ''),
       ('push_bear_qrcode', ''),
       ('is_ban_status', 0),
       ('is_namesilo', 0),
       ('namesilo_key', ''),
       ('website_logo', ''),
       ('website_home_logo', ''),
       ('nodes_detection', 0),
       ('detection_check_times', 3),
       ('is_forbid_china', 0),
       ('is_forbid_oversea', 0),
       ('AppStore_id', 0),
       ('AppStore_password', 0),
       ('is_activate_account', 0),
       ('node_daily_report', 0),
       ('mix_subscribe', 0),
       ('rand_subscribe', 0),
       ('is_custom_subscribe', 0),
       ('is_AliPay', ''),
       ('is_QQPay', ''),
       ('is_WeChatPay', ''),
       ('is_otherPay', ''),
       ('alipay_private_key', ''),
       ('alipay_public_key', ''),
       ('alipay_transport', 'http'),
       ('alipay_currency', 'USD'),
       ('bitpay_secret', ''),
       ('f2fpay_app_id', ''),
       ('f2fpay_private_key', ''),
       ('f2fpay_public_key', ''),
       ('website_security_code', ''),
       ('subject_name', ''),
       ('geetest_id', ''),
       ('geetest_key', ''),
       ('google_captcha_sitekey', ''),
       ('google_captcha_secret', ''),
       ('user_invite_days', 7),
       ('admin_invite_days', 7),
       ('offline_check_times', ''),
       ('payjs_mch_id', ''),
       ('payjs_key', ''),
       ('maintenance_mode', '0'),
       ('maintenance_time', ''),
       ('maintenance_content', ''),
       ('bark_key', ''),
       ('hcaptcha_secret', ''),
       ('hcaptcha_sitekey', ''),
       ('paypal_username', ''),
       ('paypal_password', ''),
       ('paypal_secret', ''),
       ('paypal_certificate', ''),
       ('paypal_app_id', ''),
       ('redirect_url', ''),
       ('epay_url', ''),
       ('epay_mch_id', ''),
       ('epay_key', '');


-- ----------------------------
-- Table structure for article
-- ----------------------------
CREATE TABLE `article`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                   DEFAULT '1' COMMENT '类型：1-文章、2-站内公告、3-站外公告',
    `title`      VARCHAR(100)        NOT NULL DEFAULT '' COMMENT '标题',
    `summary`    VARCHAR(255)                 DEFAULT '' COMMENT '简介',
    `logo`       VARCHAR(255)                 DEFAULT '' COMMENT 'LOGO',
    `content`    TEXT COMMENT '内容',
    `sort`       TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME            NOT NULL COMMENT '最后更新时间',
    `deleted_at` DATETIME                     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='文章';


-- ----------------------------
-- Table structure for invite
-- ----------------------------
CREATE TABLE `invite`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `inviter_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '邀请ID',
    `invitee_id` INT(10) UNSIGNED          DEFAULT NULL COMMENT '受邀ID',
    `code`       CHAR(32)         NOT NULL COMMENT '邀请码',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '邀请码状态：0-未使用、1-已使用、2-已过期',
    `dateline`   DATETIME         NOT NULL COMMENT '有效期至',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    `deleted_at` DATETIME                  DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='邀请码表';


-- ----------------------------
-- Table structure for label
-- ----------------------------
CREATE TABLE `label`
(
    `id`   INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255)        NOT NULL DEFAULT '' COMMENT '名称',
    `sort` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序值',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='标签';


-- ----------------------------
-- Records of label
-- ----------------------------
INSERT INTO `label`(`id`, `name`, `sort`)
VALUES (1, 'Netflix', 0),
       (2, 'Hulu', 0),
       (3, 'HBO', 0),
       (4, 'Amazon Video', 0),
       (5, 'DisneyNow', 0),
       (6, 'BBC', 0),
       (7, 'Channel 4', 0),
       (8, 'Fox+', 0),
       (9, 'Happyon', 0),
       (10, 'AbemeTV', 0),
       (11, 'DMM', 0),
       (12, 'Niconico', 0),
       (13, 'DAZN', 0),
       (14, 'pixiv', 0),
       (15, 'TVer', 0),
       (16, 'TVB', 0),
       (17, 'HBO Go', 0),
       (18, 'Bilibili 港澳台', 0),
       (19, 'Viu', 0),
       (20, '動畫瘋', 0),
       (21, '四季線上影視', 0),
       (22, 'LINE TV', 0),
       (23, 'Youtube Premium', 0),
       (24, '优酷', 0),
       (25, '爱奇艺', 0),
       (26, '腾讯视频', 0),
       (27, '搜狐视频', 0),
       (28, 'PP视频', 0),
       (29, '凤凰视频', 0),
       (30, '百度视频', 0),
       (31, '芒果TV', 0),
       (32, '土豆网', 0),
       (33, '哔哩哔哩', 0),
       (34, '网易云音乐', 0),
       (35, 'Bahamut', 0),
       (36, 'Deezer', 0),
       (37, 'DisneyPlus', 0),
       (38, 'HWTV', 0),
       (39, 'ITV', 0),
       (40, 'JOOX', 0),
       (41, 'KKBOX', 0),
       (42, 'KKTV', 0),
       (43, 'LiTV', 0),
       (44, 'My5', 0),
       (45, 'PBS', 0),
       (46, 'Pandora', 0),
       (47, 'SoundCloud', 0),
       (48, 'Spotify', 0),
       (49, 'TIDAL', 0),
       (50, 'TaiWanGood', 0),
       (51, 'TikTok', 0),
       (52, 'Pornhub', 0),
       (53, 'Twitch', 0),
       (54, 'ViuTV', 0),
       (55, 'encoreTVB', 0),
       (56, 'myTV_SUPER', 0),
       (57, 'niconico', 0),
       (58, 'QQ音乐', 0);


-- ----------------------------
-- Table structure for verify
-- ----------------------------
CREATE TABLE `verify`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '激活类型：1-自行激活、2-管理员激活',
    `user_id`    INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `token`      VARCHAR(32)      NOT NULL COMMENT '校验token',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='账号激活邮件地址';


-- ----------------------------
-- Table structure for verify_code
-- ----------------------------
CREATE TABLE `verify_code`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `address`    VARCHAR(128)     NOT NULL COMMENT '用户邮箱',
    `code`       CHAR(6)          NOT NULL COMMENT '验证码',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='注册激活验证码';


-- ----------------------------
-- Table structure for goods
-- ----------------------------
CREATE TABLE `goods`
(
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)        NOT NULL DEFAULT '' COMMENT '商品名称',
    `logo`        VARCHAR(255)                 DEFAULT NULL COMMENT '商品图片地址',
    `traffic`     BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品内含多少流量，单位MiB',
    `type`        TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '商品类型：1-流量包、2-套餐',
    `price`       INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '售价，单位分',
    `level`       TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买后给用户授权的等级',
    `renew`       INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '流量重置价格，单位分',
    `period`      INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '流量自动重置周期',
    `info`        VARCHAR(255)                 DEFAULT '' COMMENT '商品信息',
    `description` VARCHAR(255)                 DEFAULT '' COMMENT '商品描述',
    `days`        INT(10) UNSIGNED    NOT NULL DEFAULT '30' COMMENT '有效期',
    `invite_num`  INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '赠送邀请码数',
    `limit_num`   INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '限购数量，默认为0不限购',
    `color`       VARCHAR(50)         NOT NULL DEFAULT 'green' COMMENT '商品颜色',
    `sort`        TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
    `is_hot`      BIT                 NOT NULL DEFAULT 0 COMMENT '是否热销：0-否、1-是',
    `status`      BIT                 NOT NULL DEFAULT 1 COMMENT '状态：0-下架、1-上架',
    `created_at`  DATETIME            NOT NULL COMMENT '创建时间',
    `updated_at`  DATETIME            NOT NULL COMMENT '最后更新时间',
    `deleted_at`  DATETIME                     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='商品';


-- ----------------------------
-- Table structure for coupon
-- ----------------------------
CREATE TABLE `coupon`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(50)      NOT NULL COMMENT '优惠券名称',
    `logo`         VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '优惠券LOGO',
    `sn`           VARCHAR(50)      NOT NULL COMMENT '优惠券码',
    `type`         TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-抵用券、2-折扣券、3-充值券',
    `usable_times` SMALLINT UNSIGNED         DEFAULT NULL COMMENT '可使用次数',
    `value`        INT(10) UNSIGNED NOT NULL COMMENT '折扣金额(元)/折扣力度',
    `rule`         INT(10) UNSIGNED          DEFAULT NULL COMMENT '使用限制(元)',
    `start_time`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '有效期开始',
    `end_time`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '有效期结束',
    `status`       TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at`   DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`   DATETIME         NOT NULL COMMENT '最后更新时间',
    `deleted_at`   DATETIME                  DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unq_sn` (`sn`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='优惠券';


-- ----------------------------
-- Table structure for coupon_log
-- ----------------------------
CREATE TABLE `coupon_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠券ID',
    `goods_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
    `order_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
    `description` VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '备注',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='优惠券使用日志';


-- ----------------------------
-- Table structure for products_pool
-- ----------------------------
CREATE TABLE `products_pool`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255)     NOT NULL COMMENT '名称',
    `min_amount` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '适用最小金额，单位分',
    `max_amount` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '适用最大金额，单位分',
    `status`     BIT              NOT NULL DEFAULT 1 COMMENT '状态：0-未启用、1-已启用',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='产品名称池';


-- ----------------------------
-- Table structure for order
-- ----------------------------
CREATE TABLE `order`
(
    `id`            INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `order_sn`      VARCHAR(20)         NOT NULL DEFAULT '' COMMENT '订单编号',
    `user_id`       INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '操作人',
    `goods_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '商品ID',
    `coupon_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '优惠券ID',
    `origin_amount` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分',
    `amount`        INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '订单总价，单位分',
    `expired_at`    DATETIME                     DEFAULT NULL COMMENT '过期时间',
    `is_expire`     BIT                 NOT NULL DEFAULT 0 COMMENT '是否已过期：0-未过期、1-已过期',
    `pay_type`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal',
    `pay_way`       VARCHAR(10)         NOT NULL DEFAULT '' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等',
    `status`        TINYINT(1)          NOT NULL DEFAULT '0' COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成',
    `created_at`    DATETIME            NOT NULL COMMENT '创建时间',
    `updated_at`    DATETIME            NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    INDEX `idx_order_search` (`user_id`, `goods_id`, `is_expire`, `status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='订单';


-- ----------------------------
-- Table structure for ticket
-- ---------------------------
CREATE TABLE `ticket`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `admin_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `title`      VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '标题',
    `content`    TEXT             NOT NULL COMMENT '内容',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-待处理、1-已处理未关闭、2-已关闭',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='工单';


-- ----------------------------
-- Table structure for ticket_reply
-- ----------------------------
CREATE TABLE `ticket_reply`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '工单ID',
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复用户ID',
    `admin_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `content`    TEXT             NOT NULL COMMENT '回复内容',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='工单回复';


-- ----------------------------
-- Table structure for user_credit_log
-- ----------------------------
CREATE TABLE `user_credit_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '账号ID',
    `order_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
    `before`      INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生前余额，单位分',
    `after`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生后金额，单位分',
    `amount`      INT(10)          NOT NULL DEFAULT '0' COMMENT '发生金额，单位分',
    `description` VARCHAR(255)              DEFAULT '' COMMENT '操作描述',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户余额变动日志';


-- ----------------------------
-- Table structure for user_data_modify_log
-- ----------------------------
CREATE TABLE `user_data_modify_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `order_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生的订单ID',
    `before`      BIGINT(20)       NOT NULL DEFAULT '0' COMMENT '操作前流量',
    `after`       BIGINT(20)       NOT NULL DEFAULT '0' COMMENT '操作后流量',
    `description` VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '描述',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户流量变动日志';


-- ----------------------------
-- Table structure for referral_apply
-- ----------------------------
CREATE TABLE `referral_apply`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `before`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作前可提现金额，单位分',
    `after`      INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作后可提现金额，单位分',
    `amount`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本次提现金额，单位分',
    `link_logs`  JSON             NOT NULL COMMENT '关联返利日志ID，例如：1,3,4',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='提现申请';


-- ----------------------------
-- Table structure for referral_log
-- ----------------------------
CREATE TABLE `referral_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `invitee_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `inviter_id` INT(10) UNSIGNED NOT NULL COMMENT '推广人ID',
    `order_id`   INT(10) UNSIGNED NOT NULL COMMENT '关联订单ID',
    `amount`     INT(10) UNSIGNED NOT NULL COMMENT '消费金额，单位分',
    `commission` INT(10) UNSIGNED NOT NULL COMMENT '返利金额',
    `status`     TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '状态：0-未提现、1-审核中、2-已提现',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='消费返利日志';


-- ----------------------------
-- Table structure for notification_log
-- ----------------------------
CREATE TABLE `notification_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-ServerChan、3-Bark、4-Telegram',
    `address`    VARCHAR(255)     NOT NULL COMMENT '收信地址',
    `title`      VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '标题',
    `content`    TEXT             NOT NULL COMMENT '内容',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：-1发送失败、0-等待发送、1-发送成功',
    `error`      TEXT COMMENT '发送失败抛出的异常信息',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='通知投递记录';


-- ----------------------------
-- Table structure for email_filter
-- ----------------------------
CREATE TABLE `email_filter`
(
    `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`  TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-黑名单、2-白名单',
    `words` VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '敏感词',
    PRIMARY KEY (`id`)
) ENGINE = myisam DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='敏感词';


-- ----------------------------
-- Records of email_filter
-- ----------------------------
INSERT INTO `email_filter`(`type`, `words`)
VALUES ('1', 'chacuo.com'),
       ('1', '1766258.com'),
       ('1', '3202.com'),
       ('1', '4057.com'),
       ('1', '4059.com'),
       ('1', 'a7996.com'),
       ('1', 'bccto.me'),
       ('1', 'bnuis.com'),
       ('1', 'chaichuang.com'),
       ('1', 'cr219.com'),
       ('1', 'cuirushi.org'),
       ('1', 'dawin.com'),
       ('1', 'jiaxin8736.com'),
       ('1', 'lakqs.com'),
       ('1', 'urltc.com'),
       ('1', '027168.com'),
       ('1', '10minutemail.net'),
       ('1', '11163.com'),
       ('1', '1shivom.com'),
       ('1', 'auoie.com'),
       ('1', 'bareed.ws'),
       ('1', 'bit-degree.com'),
       ('1', 'cjpeg.com'),
       ('1', 'cool.fr.nf'),
       ('1', 'courriel.fr.nf'),
       ('1', 'disbox.net'),
       ('1', 'disbox.org'),
       ('1', 'fidelium10.com'),
       ('1', 'get365.pw'),
       ('1', 'ggr.la'),
       ('1', 'grr.la'),
       ('1', 'guerrillamail.biz'),
       ('1', 'guerrillamail.com'),
       ('1', 'guerrillamail.de'),
       ('1', 'guerrillamail.net'),
       ('1', 'guerrillamail.org'),
       ('1', 'guerrillamailblock.com'),
       ('1', 'hubii-network.com'),
       ('1', 'hurify1.com'),
       ('1', 'itoup.com'),
       ('1', 'jetable.fr.nf'),
       ('1', 'jnpayy.com'),
       ('1', 'juyouxi.com'),
       ('1', 'mail.bccto.me'),
       ('1', 'www.bccto.me'),
       ('1', 'mega.zik.dj'),
       ('1', 'moakt.co'),
       ('1', 'moakt.ws'),
       ('1', 'molms.com'),
       ('1', 'moncourrier.fr.nf'),
       ('1', 'monemail.fr.nf'),
       ('1', 'monmail.fr.nf'),
       ('1', 'nomail.xl.cx'),
       ('1', 'nospam.ze.tc'),
       ('1', 'pay-mon.com'),
       ('1', 'poly-swarm.com'),
       ('1', 'sgmh.online'),
       ('1', 'sharklasers.com'),
       ('1', 'shiftrpg.com'),
       ('1', 'spam4.me'),
       ('1', 'speed.1s.fr'),
       ('1', 'tmail.ws'),
       ('1', 'tmails.net'),
       ('1', 'tmpmail.net'),
       ('1', 'tmpmail.org'),
       ('1', 'travala10.com'),
       ('1', 'yopmail.com'),
       ('1', 'yopmail.fr'),
       ('1', 'yopmail.net'),
       ('1', 'yuoia.com'),
       ('1', 'zep-hyr.com'),
       ('1', 'zippiex.com'),
       ('1', 'lrc8.com'),
       ('1', '1otc.com'),
       ('1', 'emailna.co'),
       ('1', 'mailinator.com'),
       ('1', 'nbzmr.com'),
       ('1', 'awsoo.com'),
       ('1', 'zhcne.com'),
       ('1', '0box.eu'),
       ('1', 'contbay.com'),
       ('1', 'damnthespam.com'),
       ('1', 'kurzepost.de'),
       ('1', 'objectmail.com'),
       ('1', 'proxymail.eu'),
       ('1', 'rcpt.at'),
       ('1', 'trash-mail.at'),
       ('1', 'trashmail.at'),
       ('1', 'trashmail.com'),
       ('1', 'trashmail.io'),
       ('1', 'trashmail.me'),
       ('1', 'trashmail.net'),
       ('1', 'wegwerfmail.de'),
       ('1', 'wegwerfmail.net'),
       ('1', 'wegwerfmail.org'),
       ('1', 'nwytg.net'),
       ('1', 'despam.it'),
       ('1', 'spambox.us'),
       ('1', 'spam.la'),
       ('1', 'mytrashmail.com'),
       ('1', 'mt2014.com'),
       ('1', 'mt2015.com'),
       ('1', 'thankyou2010.com'),
       ('1', 'trash2009.com'),
       ('1', 'mt2009.com'),
       ('1', 'trashymail.com'),
       ('1', 'tempemail.net'),
       ('1', 'slopsbox.com'),
       ('1', 'mailnesia.com'),
       ('1', 'ezehe.com'),
       ('1', 'tempail.com'),
       ('1', 'newairmail.com'),
       ('1', 'temp-mail.org'),
       ('1', 'linshiyouxiang.net'),
       ('1', 'zwoho.com'),
       ('1', 'mailboxy.fun'),
       ('1', 'crypto-net.club'),
       ('1', 'guerrillamail.info'),
       ('1', 'pokemail.net'),
       ('1', 'odmail.cn'),
       ('1', 'hlooy.com'),
       ('1', 'ozlaq.com'),
       ('1', '666email.com'),
       ('1', 'linshiyou.com'),
       ('1', 'linshiyou.pl'),
       ('1', 'woyao.pl'),
       ('1', 'yaowo.pl'),
       ('2', 'qq.com'),
       ('2', '163.com'),
       ('2', '126.com'),
       ('2', '189.com'),
       ('2', 'sohu.com'),
       ('2', 'gmail.com'),
       ('2', 'outlook.com'),
       ('2', 'icloud.com');


-- ----------------------------
-- Table structure for user_subscribe
-- ----------------------------
CREATE TABLE `user_subscribe`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `code`       CHAR(8)          NOT NULL DEFAULT '' COMMENT '订阅地址唯一识别码',
    `times`      INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '地址请求次数',
    `status`     BIT              NOT NULL DEFAULT 1 COMMENT '状态：0-禁用、1-启用',
    `ban_time`   INT(10) UNSIGNED          DEFAULT NULL COMMENT '封禁时间',
    `ban_desc`   VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '封禁理由',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    INDEX `user_id` (`user_id`, `status`),
    INDEX `code` (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户订阅';


-- ----------------------------
-- Records of user_subscribe
-- ----------------------------
INSERT INTO `user_subscribe`(`id`, `user_id`, `code`, `created_at`, `updated_at`)
VALUES ('1', '1', 'SsXa1', Now(), Now());


-- ----------------------------
-- Table structure for user_subscribe_log
-- ----------------------------
CREATE TABLE `user_subscribe_log`
(
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_subscribe_id` INT(10) UNSIGNED NOT NULL COMMENT '对应user_subscribe的id',
    `request_ip`        CHAR(45) DEFAULT NULL COMMENT '请求IP',
    `request_time`      DATETIME         NOT NULL COMMENT '请求时间',
    `request_header`    TEXT COMMENT '请求头部信息',
    PRIMARY KEY (`id`),
    INDEX `user_subscribe_id` (`user_subscribe_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户订阅访问日志';


-- ----------------------------
-- Table structure for user_daily_data_flow
-- ----------------------------
CREATE TABLE `user_daily_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    `u`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255)                 DEFAULT '' COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_node` (`user_id`, `node_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户每日流量统计';


-- ----------------------------
-- Table structure for user_hourly_data_flow
-- ----------------------------
CREATE TABLE `user_hourly_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    `u`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255)                 DEFAULT '' COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_node` (`user_id`, `node_id`) USING BTREE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户每小时流量统计';


-- ----------------------------
-- Table structure for node_daily_data_flow
-- ----------------------------
CREATE TABLE `node_daily_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID',
    `u`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255)                 DEFAULT '' COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_node_id` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点每日流量统计';


-- ----------------------------
-- Table structure for node_hourly_data_flow
-- ----------------------------
CREATE TABLE `node_hourly_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID',
    `u`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255)                 DEFAULT '' COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_node_id` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点每小时流量统计';


-- ----------------------------
-- Table structure for user_baned_log
-- ----------------------------
CREATE TABLE `user_baned_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `time`        INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封禁账号时长，单位分钟',
    `description` VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '操作描述',
    `status`      BIT              NOT NULL DEFAULT 0 COMMENT '状态：0-未处理、1-已处理',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`  DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户封禁日志';


-- ----------------------------
-- Table structure for country
-- ----------------------------
CREATE TABLE `country`
(
    `code` CHAR(2)     NOT NULL COMMENT 'ISO国家代码',
    `name` VARCHAR(10) NOT NULL COMMENT '名称',
    PRIMARY KEY (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='国家代码';


-- ----------------------------
-- Records of country
-- ----------------------------
INSERT INTO `country`(`code`, `name`)
VALUES ('au', '澳大利亚'),
       ('br', '巴西'),
       ('ca', '加拿大'),
       ('ch', '瑞士'),
       ('cn', '中国'),
       ('de', '德国'),
       ('dk', '丹麦'),
       ('eg', '埃及'),
       ('fr', '法国'),
       ('gr', '希腊'),
       ('hk', '香港'),
       ('id', '印度尼西亚'),
       ('ie', '爱尔兰'),
       ('il', '以色列'),
       ('in', '印度'),
       ('iq', '伊拉克'),
       ('ir', '伊朗'),
       ('it', '意大利'),
       ('jp', '日本'),
       ('kr', '韩国'),
       ('mx', '墨西哥'),
       ('my', '马来西亚'),
       ('nl', '荷兰'),
       ('no', '挪威'),
       ('nz', '纽西兰'),
       ('ph', '菲律宾'),
       ('ru', '俄罗斯'),
       ('se', '瑞典'),
       ('sg', '新加坡'),
       ('th', '泰国'),
       ('tr', '土耳其'),
       ('tw', '台湾'),
       ('uk', '英国'),
       ('us', '美国'),
       ('vn', '越南'),
       ('pl', '波兰'),
       ('kz', '哈萨克斯坦'),
       ('ua', '乌克兰'),
       ('ro', '罗马尼亚'),
       ('ae', '阿联酋'),
       ('za', '南非'),
       ('mm', '缅甸'),
       ('is', '冰岛'),
       ('fi', '芬兰'),
       ('lu', '卢森堡'),
       ('be', '比利时'),
       ('bg', '保加利亚'),
       ('lt', '立陶宛'),
       ('co', '哥伦比亚'),
       ('mo', '澳门'),
       ('ke', '肯尼亚'),
       ('cz', '捷克'),
       ('md', '摩尔多瓦'),
       ('es', '西班牙'),
       ('pk', '巴基斯坦'),
       ('pt', '葡萄牙'),
       ('hu', '匈牙利'),
       ('ar', '阿根廷');


-- ----------------------------
-- Table structure for payment
-- ----------------------------
CREATE TABLE `payment`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `trade_no`   VARCHAR(64)      NOT NULL COMMENT '支付单号（本地订单号）',
    `user_id`    INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `order_id`   INT(10) UNSIGNED NOT NULL COMMENT '本地订单ID',
    `amount`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '金额，单位分',
    `qr_code`    TEXT COMMENT '支付二维码',
    `url`        TEXT COMMENT '支付链接',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '支付状态：-1-支付失败、0-等待支付、1-支付成功',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='支付单';


-- ----------------------------
-- Table structure for payment_callback
-- ----------------------------
CREATE TABLE `payment_callback`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `trade_no`     VARCHAR(64)      NOT NULL COMMENT '本地订单号',
    `out_trade_no` VARCHAR(64)      NOT NULL COMMENT '外部订单号（支付平台）',
    `amount`       INT(10) UNSIGNED NOT NULL COMMENT '交易金额，单位分',
    `status`       BIT              NOT NULL COMMENT '交易状态：0-失败、1-成功',
    `created_at`   DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`   DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='支付回调日志';


-- ----------------------------
-- Table structure for marketing
-- ----------------------------
CREATE TABLE `marketing`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)       NOT NULL COMMENT '类型：1-邮件群发',
    `receiver`   TEXT             NOT NULL COMMENT '接收者',
    `title`      VARCHAR(255)     NOT NULL COMMENT '标题',
    `content`    TEXT             NOT NULL COMMENT '内容',
    `error`      VARCHAR(255)     NULL COMMENT '错误信息',
    `status`     TINYINT(1)       NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='营销';


-- ----------------------------
-- Table structure for user_login_log
-- ----------------------------
CREATE TABLE `user_login_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `ip`         VARCHAR(45)      NOT NULL COMMENT 'IP地址',
    `country`    VARCHAR(128)     NOT NULL COMMENT '国家',
    `province`   VARCHAR(128)     NOT NULL COMMENT '省份',
    `city`       VARCHAR(128)     NOT NULL COMMENT '城市',
    `county`     VARCHAR(128)     NOT NULL COMMENT '郡县',
    `isp`        VARCHAR(128)     NOT NULL COMMENT '运营商',
    `area`       VARCHAR(255)     NOT NULL COMMENT '地区',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户登录日志';


-- ----------------------------
-- Table structure for ss_node_ip
-- ----------------------------
CREATE TABLE `ss_node_ip`
(
    `id`         INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '节点ID',
    `user_id`    INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '用户ID',
    `port`       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '端口',
    `type`       CHAR(3)              NOT NULL DEFAULT 'tcp' COMMENT '类型：all、tcp、udp',
    `ip`         TEXT COMMENT '连接IP：每个IP用,号隔开',
    `created_at` INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '上报时间',
    PRIMARY KEY (`id`),
    KEY `idx_port` (`port`),
    KEY `idx_node` (`node_id`),
    KEY `idx_user` (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户连接IP';


-- ----------------------------
-- Table structure for rule
-- ----------------------------
CREATE TABLE `rule`
(
    `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`    TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-正则表达式、2-域名、3-IP、4-协议',
    `name`    VARCHAR(100)     NOT NULL COMMENT '规则描述',
    `pattern` TEXT             NOT NULL COMMENT '规则值',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则';


-- ----------------------------
-- Records of rule
-- ----------------------------
INSERT INTO `rule`(`id`, `type`, `name`, `pattern`)
VALUES (1, '1', '360',
        '(.*\.||)(^360|0360|1360|3600|360safe|^so|qhimg|qhmsg|^yunpan|qihoo|qhcdn|qhupdate|360totalsecurity|360shouji|qihucdn|360kan|secmp)\.(cn|com|net)'),
       (2, '1', '腾讯管家', '(\.guanjia\.qq\.com|qqpcmgr|QQPCMGR)'),
       (3, '1', '金山毒霸', '(.*\.||)(rising|kingsoft|duba|xindubawukong|jinshanduba)\.(com|net|org)'),
       (4, '1', '暗网相关', '(.*\.||)(netvigator|torproject)\.(cn|com|net|org)'),
       (5, '1', '百度定位',
        '(api|ps|sv|offnavi|newvector|ulog\\.imap|newloc|tracknavi)(\\.map|)\\.(baidu|n\\.shifen)\\.com'),
       (6, '1', '法轮功类',
        '(.*\\.||)(dafahao|minghui|dongtaiwang|dajiyuan|falundata|shenyun|tuidang|epochweekly|epochtimes|ntdtv|falundafa|wujieliulan|zhengjian)\\.(org|com|net)'),
       (7, '1', 'BT扩展名',
        '(torrent|\\.torrent|peer_id=|info_hash|get_peers|find_node|BitTorrent|announce_peer|announce\\.php\\?passkey=)'),
       (8, '1', '邮件滥发',
        '((^.*\@)(guerrillamail|guerrillamailblock|sharklasers|grr|pokemail|spam4|bccto|chacuo|027168)\.(info|biz|com|de|net|org|me|la)|Subject|HELO|SMTP)'),
       (9, '1', '迅雷下载', '(.?)(xunlei|sandai|Thunder|XLLiveUD)(.)'),
       (10, '1', '大陆应用',
        '(.*\\.||)(baidu|qq|163|189|10000|10010|10086|sohu|sogoucdn|sogou|uc|58|taobao|qpic|bilibili|hdslb|acgvideo|sina|douban|doubanio|xiaohongshu|sinaimg|weibo|xiaomi|youzanyun|meituan|dianping|biliapi|huawei|pinduoduo|cnzz)\\.(org|com|net|cn)'),
       (11, '1', '大陆银行',
        '(.*\\.||)(icbc|ccb|boc|bankcomm|abchina|cmbchina|psbc|cebbank|cmbc|pingan|spdb|citicbank|cib|hxb|bankofbeijing|hsbank|tccb|4001961200|bosc|hkbchina|njcb|nbcb|lj-bank|bjrcb|jsbchina|gzcb|cqcbank|czbank|hzbank|srcb|cbhb|cqrcb|grcbank|qdccb|bocd|hrbcb|jlbank|bankofdl|qlbchina|dongguanbank|cscb|hebbank|drcbank|zzbank|bsb|xmccb|hljrcc|jxnxs|gsrcu|fjnx|sxnxs|gx966888|gx966888|zj96596|hnnxs|ahrcu|shanxinj|hainanbank|scrcu|gdrcu|hbxh|ynrcc|lnrcc|nmgnxs|hebnx|jlnls|js96008|hnnx|sdnxs)\\.(org|com|net|cn)'),
       (12, '1', '台湾银行',
        '(.*\\.||)(firstbank|bot|cotabank|megabank|tcb-bank|landbank|hncb|bankchb|tbb|ktb|tcbbank|scsb|bop|sunnybank|kgibank|fubon|ctbcbank|cathaybk|eximbank|bok|ubot|feib|yuantabank|sinopac|esunbank|taishinbank|jihsunbank|entiebank|hwataibank|csc|skbank)\\.(org|com|net|tw)'),
       (13, '1', '大陆第三方支付',
        '(.*\\.||)(alipay|baifubao|yeepay|99bill|95516|51credit|cmpay|tenpay|lakala|jdpay)\\.(org|com|net|cn)'),
       (14, '1', '台湾特供', '(.*\.||)(visa|mycard|mastercard|gov|gash|beanfun|bank|line)\.(org|com|net|cn|tw|jp|kr)'),
       (15, '1', '涉政治类',
        '(.*\\.||)(shenzhoufilm|secretchina|renminbao|aboluowang|mhradio|guangming|zhengwunet|soundofhope|yuanming|zhuichaguoji|fgmtv|xinsheng|shenyunperformingarts|epochweekly|tuidang|shenyun|falundata|bannedbook|pincong|rfi|mingjingnews|boxun|rfa|scmp|ogate|voachinese)\\.(org|com|net|rocks|fr)'),
       (16, '1', '流媒体',
        '(.*\.||)(youtube|googlevideo|hulu|netflix|nflxvideo|akamai|nflximg|hbo|mtv|bbc|tvb)\.(org|club|com|net|tv)'),
       (17, '1', '测速类', '(.*\.||)(fast|speedtest)\.(org|com|net|cn)'),
       (18, '1', '外汇交易类', '(.*\.||)(metatrader4|metatrader5|mql5)\.(org|com|net)');


-- ----------------------------
-- Table structure for rule_group
-- ----------------------------
CREATE TABLE `rule_group`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       BIT              NOT NULL DEFAULT 1 COMMENT '模式：1-阻断、0-放行',
    `name`       VARCHAR(255)     NOT NULL COMMENT '分组名称',
    `rules`      JSON                      DEFAULT NULL COMMENT '关联的规则ID，多个用,号分隔',
    `nodes`      JSON                      DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则分组';


-- ----------------------------
-- Table structure for rule_group_node
-- ----------------------------
CREATE TABLE `rule_group_node`
(
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_group_id` INT(10) UNSIGNED NOT NULL COMMENT '规则分组ID',
    `node_id`       INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    `created_at`    DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`    DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则分组节点关联表';


-- ----------------------------
-- Table structure for rule_log
-- ----------------------------
CREATE TABLE `rule_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址',
    `reason`     VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '触发原因',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx` (`user_id`, `node_id`, `rule_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='触发审计规则日志表';


-- ----------------------------
-- Table structure for node_rule
-- ----------------------------
CREATE TABLE `node_rule`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NULL COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED NULL COMMENT '审计规则ID',
    `is_black`   BIT              NOT NULL DEFAULT 1 COMMENT '是否黑名单模式：0-不是、1-是',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点审计规则关联';


-- ----------------------------
-- Table structure for `node_auth`
-- ----------------------------
CREATE TABLE `node_auth`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NOT NULL COMMENT '授权节点ID',
    `key`        CHAR(16)         NOT NULL COMMENT '认证KEY',
    `secret`     CHAR(8)          NOT NULL COMMENT '通信密钥',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    INDEX `id` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点授权密钥表';


-- ----------------------------
-- Table structure for `node_certificate`
-- ----------------------------
CREATE TABLE `node_certificate`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `domain`     VARCHAR(255)     NOT NULL COMMENT '域名',
    `key`        TEXT             NULL COMMENT '域名证书KEY',
    `pem`        TEXT             NULL COMMENT '域名证书PEM',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB COLLATE = 'utf8mb4_unicode_ci' COMMENT ='域名证书';


-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
CREATE TABLE `failed_jobs`
(
    `id`         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `connection` TEXT                NOT NULL,
    `queue`      TEXT                NOT NULL,
    `payload`    LONGTEXT            NOT NULL,
    `exception`  LONGTEXT            NOT NULL,
    `failed_at`  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='失败任务';


-- ----------------------------
-- Table structure for jobs
-- ----------------------------
CREATE TABLE `jobs`
(
    `id`           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255)        NOT NULL,
    `payload`      LONGTEXT            NOT NULL,
    `attempts`     TINYINT(3) UNSIGNED NOT NULL,
    `reserved_at`  INT(10) UNSIGNED DEFAULT NULL,
    `available_at` INT(10) UNSIGNED    NOT NULL,
    `created_at`   INT(10) UNSIGNED    NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='任务';


-- ----------------------------
-- Table structure for migrations
-- ----------------------------
CREATE TABLE `migrations`
(
    `id`        INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255)     NOT NULL,
    `batch`     INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='迁移';


/*!40111 SET SQL_NOTES = @old_sql_notes */;
/*!40101 SET SQL_MODE = @old_sql_mode */;
/*!40014 SET FOREIGN_KEY_CHECKS = @old_foreign_key_checks */;
/*!40101 SET CHARACTER_SET_CLIENT = @old_character_set_client */;
/*!40101 SET CHARACTER_SET_RESULTS = @old_character_set_results */;
/*!40101 SET COLLATION_CONNECTION = @old_collation_connection */;