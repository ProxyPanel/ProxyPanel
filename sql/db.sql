-- Adminer 4.7.7 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE TABLE `article`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '类型：1-文章、2-站内公告、3-站外公告',
    `title`      VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
    `summary`    VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '简介',
    `logo`       VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT 'LOGO',
    `content`    TEXT COLLATE utf8mb4_unicode_ci COMMENT '内容',
    `sort`       TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '排序',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    `deleted_at` TIMESTAMP                               NULL     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `config`
(
    `name`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名',
    `value` TEXT COLLATE utf8mb4_unicode_ci COMMENT '配置值',
    PRIMARY KEY (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `config` (`name`, `value`)
VALUES ('active_times', '3'),
       ('admin_invite_days', '7'),
       ('alipay_currency', 'USD'),
       ('alipay_private_key', NULL),
       ('alipay_public_key', NULL),
       ('alipay_qrcode', NULL),
       ('alipay_transport', 'http'),
       ('AppStore_id', NULL),
       ('AppStore_password', NULL),
       ('auto_release_port', '1'),
       ('bark_key', NULL),
       ('bitpay_secret', NULL),
       ('codepay_id', NULL),
       ('codepay_key', NULL),
       ('codepay_url', NULL),
       ('default_days', '7'),
       ('default_traffic', '1024'),
       ('detection_check_times', '3'),
       ('epay_key', NULL),
       ('epay_mch_id', NULL),
       ('epay_url', NULL),
       ('expire_days', '15'),
       ('expire_warning', NULL),
       ('f2fpay_app_id', NULL),
       ('f2fpay_private_key', NULL),
       ('f2fpay_public_key', NULL),
       ('geetest_id', NULL),
       ('geetest_key', NULL),
       ('google_captcha_secret', NULL),
       ('google_captcha_sitekey', NULL),
       ('hcaptcha_secret', NULL),
       ('hcaptcha_sitekey', NULL),
       ('invite_num', '3'),
       ('is_activate_account', NULL),
       ('is_AliPay', NULL),
       ('is_ban_status', NULL),
       ('is_captcha', NULL),
       ('is_checkin', '1'),
       ('is_clear_log', '1'),
       ('is_custom_subscribe', NULL),
       ('is_email_filtering', NULL),
       ('is_forbid_china', NULL),
       ('is_forbid_oversea', NULL),
       ('is_forbid_robot', NULL),
       ('is_free_code', NULL),
       ('is_invite_register', '2'),
       ('is_namesilo', NULL),
       ('is_node_offline', NULL),
       ('is_notification', NULL),
       ('is_otherPay', NULL),
       ('is_push_bear', NULL),
       ('is_QQPay', NULL),
       ('is_rand_port', NULL),
       ('is_register', '1'),
       ('is_reset_password', '1'),
       ('is_subscribe_ban', '1'),
       ('is_traffic_ban', '1'),
       ('is_user_rand_port', NULL),
       ('is_WeChatPay', NULL),
       ('maintenance_content', NULL),
       ('maintenance_mode', NULL),
       ('maintenance_time', NULL),
       ('max_port', '65535'),
       ('max_rand_traffic', '500'),
       ('min_port', '10000'),
       ('min_rand_traffic', '10'),
       ('mix_subscribe', NULL),
       ('namesilo_key', NULL),
       ('node_daily_report', NULL),
       ('nodes_detection', NULL),
       ('offline_check_times', NULL),
       ('payjs_key', NULL),
       ('payjs_mch_id', NULL),
       ('paypal_app_id', NULL),
       ('paypal_certificate', NULL),
       ('paypal_password', NULL),
       ('paypal_secret', NULL),
       ('paypal_username', NULL),
       ('push_bear_qrcode', NULL),
       ('push_bear_send_key', NULL),
       ('rand_subscribe', NULL),
       ('redirect_url', NULL),
       ('referral_money', '100'),
       ('referral_percent', '0.2'),
       ('referral_status', '1'),
       ('referral_traffic', '1024'),
       ('referral_type', NULL),
       ('register_ip_limit', '5'),
       ('reset_password_times', '3'),
       ('reset_traffic', '1'),
       ('server_chan_key', NULL),
       ('subject_name', NULL),
       ('subscribe_ban_times', '20'),
       ('subscribe_domain', NULL),
       ('subscribe_max', '3'),
       ('traffic_ban_time', '60'),
       ('traffic_ban_value', '10'),
       ('traffic_limit_time', '1440'),
       ('traffic_warning', NULL),
       ('traffic_warning_percent', '80'),
       ('trojan_license', NULL),
       ('user_invite_days', '7'),
       ('v2ray_license', NULL),
       ('v2ray_tls_provider', NULL),
       ('web_api_url', NULL),
       ('webmaster_email', NULL),
       ('website_analytics', NULL),
       ('website_callback_url', NULL),
       ('website_customer_service', NULL),
       ('website_home_logo', NULL),
       ('website_logo', NULL),
       ('website_name', 'ProxyPanel'),
       ('website_security_code', NULL),
       ('website_url', 'https://demo.proxypanel.ml'),
       ('wechat_qrcode', NULL);

CREATE TABLE `country`
(
    `code` CHAR(2) COLLATE utf8mb4_unicode_ci     NOT NULL COMMENT 'ISO国家代码',
    `name` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    PRIMARY KEY (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `country` (`code`, `name`)
VALUES ('ae', '阿联酋'),
       ('ar', '阿根廷'),
       ('au', '澳大利亚'),
       ('be', '比利时'),
       ('bg', '保加利亚'),
       ('br', '巴西'),
       ('ca', '加拿大'),
       ('ch', '瑞士'),
       ('cn', '中国'),
       ('co', '哥伦比亚'),
       ('cz', '捷克'),
       ('de', '德国'),
       ('dk', '丹麦'),
       ('eg', '埃及'),
       ('es', '西班牙'),
       ('fi', '芬兰'),
       ('fr', '法国'),
       ('gr', '希腊'),
       ('hk', '香港'),
       ('hu', '匈牙利'),
       ('id', '印度尼西亚'),
       ('ie', '爱尔兰'),
       ('il', '以色列'),
       ('in', '印度'),
       ('iq', '伊拉克'),
       ('ir', '伊朗'),
       ('is', '冰岛'),
       ('it', '意大利'),
       ('jp', '日本'),
       ('ke', '肯尼亚'),
       ('kr', '韩国'),
       ('kz', '哈萨克斯坦'),
       ('lt', '立陶宛'),
       ('lu', '卢森堡'),
       ('md', '摩尔多瓦'),
       ('mm', '缅甸'),
       ('mo', '澳门'),
       ('mx', '墨西哥'),
       ('my', '马来西亚'),
       ('nl', '荷兰'),
       ('no', '挪威'),
       ('nz', '纽西兰'),
       ('ph', '菲律宾'),
       ('pk', '巴基斯坦'),
       ('pl', '波兰'),
       ('pt', '葡萄牙'),
       ('ro', '罗马尼亚'),
       ('ru', '俄罗斯'),
       ('se', '瑞典'),
       ('sg', '新加坡'),
       ('th', '泰国'),
       ('tr', '土耳其'),
       ('tw', '台湾'),
       ('ua', '乌克兰'),
       ('uk', '英国'),
       ('us', '美国'),
       ('vn', '越南'),
       ('za', '南非');

CREATE TABLE `coupon`
(
    `id`           INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '优惠券名称',
    `logo`         VARCHAR(255) COLLATE utf8mb4_unicode_ci         DEFAULT NULL COMMENT '优惠券LOGO',
    `sn`           VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '优惠券码',
    `type`         TINYINT(1)                             NOT NULL DEFAULT '1' COMMENT '类型：1-抵用券、2-折扣券、3-充值券',
    `usable_times` SMALLINT(5) UNSIGNED                            DEFAULT NULL COMMENT '可使用次数',
    `value`        INT(10) UNSIGNED                       NOT NULL COMMENT '折扣金额(元)/折扣力度',
    `rule`         INT(10) UNSIGNED                                DEFAULT NULL COMMENT '使用限制(元)',
    `start_time`   INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '有效期开始',
    `end_time`     INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '有效期结束',
    `status`       TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at`   DATETIME                               NOT NULL COMMENT '创建时间',
    `updated_at`   DATETIME                               NOT NULL COMMENT '最后更新时间',
    `deleted_at`   TIMESTAMP                              NULL     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `coupon_sn_unique` (`sn`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `coupon_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id`   INT(10) UNSIGNED NOT NULL              DEFAULT '0' COMMENT '优惠券ID',
    `goods_id`    INT(10) UNSIGNED NOT NULL              DEFAULT '0' COMMENT '商品ID',
    `order_id`    INT(10) UNSIGNED NOT NULL              DEFAULT '0' COMMENT '订单ID',
    `description` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `email_filter`
(
    `id`    INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `type`  TINYINT(1)                             NOT NULL DEFAULT '1' COMMENT '类型：1-黑名单、2-白名单',
    `words` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '敏感词',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `email_filter` (`id`, `type`, `words`)
VALUES (1, 1, 'chacuo.com'),
       (2, 1, '1766258.com'),
       (3, 1, '3202.com'),
       (4, 1, '4057.com'),
       (5, 1, '4059.com'),
       (6, 1, 'a7996.com'),
       (7, 1, 'bccto.me'),
       (8, 1, 'bnuis.com'),
       (9, 1, 'chaichuang.com'),
       (10, 1, 'cr219.com'),
       (11, 1, 'cuirushi.org'),
       (12, 1, 'dawin.com'),
       (13, 1, 'jiaxin8736.com'),
       (14, 1, 'lakqs.com'),
       (15, 1, 'urltc.com'),
       (16, 1, '027168.com'),
       (17, 1, '10minutemail.net'),
       (18, 1, '11163.com'),
       (19, 1, '1shivom.com'),
       (20, 1, 'auoie.com'),
       (21, 1, 'bareed.ws'),
       (22, 1, 'bit-degree.com'),
       (23, 1, 'cjpeg.com'),
       (24, 1, 'cool.fr.nf'),
       (25, 1, 'courriel.fr.nf'),
       (26, 1, 'disbox.net'),
       (27, 1, 'disbox.org'),
       (28, 1, 'fidelium10.com'),
       (29, 1, 'get365.pw'),
       (30, 1, 'ggr.la'),
       (31, 1, 'grr.la'),
       (32, 1, 'guerrillamail.biz'),
       (33, 1, 'guerrillamail.com'),
       (34, 1, 'guerrillamail.de'),
       (35, 1, 'guerrillamail.net'),
       (36, 1, 'guerrillamail.org'),
       (37, 1, 'guerrillamailblock.com'),
       (38, 1, 'hubii-network.com'),
       (39, 1, 'hurify1.com'),
       (40, 1, 'itoup.com'),
       (41, 1, 'jetable.fr.nf'),
       (42, 1, 'jnpayy.com'),
       (43, 1, 'juyouxi.com'),
       (44, 1, 'mail.bccto.me'),
       (45, 1, 'www.bccto.me'),
       (46, 1, 'mega.zik.dj'),
       (47, 1, 'moakt.co'),
       (48, 1, 'moakt.ws'),
       (49, 1, 'molms.com'),
       (50, 1, 'moncourrier.fr.nf'),
       (51, 1, 'monemail.fr.nf'),
       (52, 1, 'monmail.fr.nf'),
       (53, 1, 'nomail.xl.cx'),
       (54, 1, 'nospam.ze.tc'),
       (55, 1, 'pay-mon.com'),
       (56, 1, 'poly-swarm.com'),
       (57, 1, 'sgmh.online'),
       (58, 1, 'sharklasers.com'),
       (59, 1, 'shiftrpg.com'),
       (60, 1, 'spam4.me'),
       (61, 1, 'speed.1s.fr'),
       (62, 1, 'tmail.ws'),
       (63, 1, 'tmails.net'),
       (64, 1, 'tmpmail.net'),
       (65, 1, 'tmpmail.org'),
       (66, 1, 'travala10.com'),
       (67, 1, 'yopmail.com'),
       (68, 1, 'yopmail.fr'),
       (69, 1, 'yopmail.net'),
       (70, 1, 'yuoia.com'),
       (71, 1, 'zep-hyr.com'),
       (72, 1, 'zippiex.com'),
       (73, 1, 'lrc8.com'),
       (74, 1, '1otc.com'),
       (75, 1, 'emailna.co'),
       (76, 1, 'mailinator.com'),
       (77, 1, 'nbzmr.com'),
       (78, 1, 'awsoo.com'),
       (79, 1, 'zhcne.com'),
       (80, 1, '0box.eu'),
       (81, 1, 'contbay.com'),
       (82, 1, 'damnthespam.com'),
       (83, 1, 'kurzepost.de'),
       (84, 1, 'objectmail.com'),
       (85, 1, 'proxymail.eu'),
       (86, 1, 'rcpt.at'),
       (87, 1, 'trash-mail.at'),
       (88, 1, 'trashmail.at'),
       (89, 1, 'trashmail.com'),
       (90, 1, 'trashmail.io'),
       (91, 1, 'trashmail.me'),
       (92, 1, 'trashmail.net'),
       (93, 1, 'wegwerfmail.de'),
       (94, 1, 'wegwerfmail.net'),
       (95, 1, 'wegwerfmail.org'),
       (96, 1, 'nwytg.net'),
       (97, 1, 'despam.it'),
       (98, 1, 'spambox.us'),
       (99, 1, 'spam.la'),
       (100, 1, 'mytrashmail.com'),
       (101, 1, 'mt2014.com'),
       (102, 1, 'mt2015.com'),
       (103, 1, 'thankyou2010.com'),
       (104, 1, 'trash2009.com'),
       (105, 1, 'mt2009.com'),
       (106, 1, 'trashymail.com'),
       (107, 1, 'tempemail.net'),
       (108, 1, 'slopsbox.com'),
       (109, 1, 'mailnesia.com'),
       (110, 1, 'ezehe.com'),
       (111, 1, 'tempail.com'),
       (112, 1, 'newairmail.com'),
       (113, 1, 'temp-mail.org'),
       (114, 1, 'linshiyouxiang.net'),
       (115, 1, 'zwoho.com'),
       (116, 1, 'mailboxy.fun'),
       (117, 1, 'crypto-net.club'),
       (118, 1, 'guerrillamail.info'),
       (119, 1, 'pokemail.net'),
       (120, 1, 'odmail.cn'),
       (121, 1, 'hlooy.com'),
       (122, 1, 'ozlaq.com'),
       (123, 1, '666email.com'),
       (124, 1, 'linshiyou.com'),
       (125, 1, 'linshiyou.pl'),
       (126, 1, 'woyao.pl'),
       (127, 1, 'yaowo.pl'),
       (128, 2, 'qq.com'),
       (129, 2, '163.com'),
       (130, 2, '126.com'),
       (131, 2, '189.com'),
       (132, 2, 'sohu.com'),
       (133, 2, 'gmail.com'),
       (134, 2, 'outlook.com'),
       (135, 2, 'icloud.com');

CREATE TABLE `failed_jobs`
(
    `id`         BIGINT(20) UNSIGNED                 NOT NULL AUTO_INCREMENT,
    `connection` TEXT COLLATE utf8mb4_unicode_ci     NOT NULL,
    `queue`      TEXT COLLATE utf8mb4_unicode_ci     NOT NULL,
    `payload`    LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    `exception`  LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    `failed_at`  TIMESTAMP                           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `goods`
(
    `id`          INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品名称',
    `logo`        VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '商品图片地址',
    `traffic`     BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '商品内含多少流量，单位MiB',
    `type`        TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '商品类型：1-流量包、2-套餐',
    `price`       INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '售价，单位分',
    `level`       TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '购买后给用户授权的等级',
    `renew`       INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '流量重置价格，单位分',
    `period`      INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '流量自动重置周期',
    `info`        VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '商品信息',
    `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '商品描述',
    `days`        INT(10) UNSIGNED                        NOT NULL DEFAULT '30' COMMENT '有效期',
    `invite_num`  INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '赠送邀请码数',
    `limit_num`   INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '限购数量，默认为null不限购',
    `color`       VARCHAR(50) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'green' COMMENT '商品颜色',
    `sort`        TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '排序',
    `is_hot`      TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '是否热销：0-否、1-是',
    `status`      TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '状态：0-下架、1-上架',
    `created_at`  DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at`  DATETIME                                NOT NULL COMMENT '最后更新时间',
    `deleted_at`  TIMESTAMP                               NULL     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `invite`
(
    `id`         INT(10) UNSIGNED                    NOT NULL AUTO_INCREMENT,
    `inviter_id` INT(10) UNSIGNED                    NOT NULL DEFAULT '0' COMMENT '邀请ID',
    `invitee_id` INT(10) UNSIGNED                             DEFAULT NULL COMMENT '受邀ID',
    `code`       CHAR(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邀请码',
    `status`     TINYINT(1)                          NOT NULL DEFAULT '0' COMMENT '邀请码状态：0-未使用、1-已使用、2-已过期',
    `dateline`   DATETIME                            NOT NULL COMMENT '有效期至',
    `created_at` DATETIME                            NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                            NOT NULL COMMENT '最后更新时间',
    `deleted_at` TIMESTAMP                           NULL     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `invite_code_unique` (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `jobs`
(
    `id`           BIGINT(20) UNSIGNED                     NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `payload`      LONGTEXT COLLATE utf8mb4_unicode_ci     NOT NULL,
    `attempts`     TINYINT(3) UNSIGNED                     NOT NULL,
    `reserved_at`  INT(10) UNSIGNED DEFAULT NULL,
    `available_at` INT(10) UNSIGNED                        NOT NULL,
    `created_at`   INT(10) UNSIGNED                        NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `label`
(
    `id`   INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `sort` TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '排序值',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `label` (`id`, `name`, `sort`)
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
       (12, 'NicoNico', 0),
       (13, 'Pixiv', 0),
       (14, 'TVer', 0),
       (15, 'TVB', 0),
       (16, 'HBO Go', 0),
       (17, 'BiliBili港澳台', 0),
       (18, '動畫瘋', 0),
       (19, '四季線上影視', 0),
       (20, 'LINE TV', 0),
       (21, 'Youtube Premium', 0),
       (22, '中国视频网站', 0),
       (23, '网易云音乐', 0),
       (24, 'QQ音乐', 0),
       (25, 'DisneyPlus', 0),
       (26, 'Pandora', 0),
       (27, 'SoundCloud', 0),
       (28, 'Spotify', 0),
       (29, 'TIDAL', 0),
       (30, 'TikTok', 0),
       (31, 'Pornhub', 0),
       (32, 'Twitch', 0);

CREATE TABLE `level`
(
    `id`    INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `level` TINYINT(3) UNSIGNED                     NOT NULL COMMENT '等级',
    `name`  VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '等级名称',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `level` (`id`, `level`, `name`)
VALUES (1, 0, 'Free'),
       (2, 1, 'VIP-1'),
       (3, 2, 'VIP-2'),
       (4, 3, 'VIP-3'),
       (5, 4, 'VIP-4'),
       (6, 5, 'VIP-5'),
       (7, 6, 'VIP-6'),
       (8, 7, 'VIP-7');

CREATE TABLE `marketing`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                              NOT NULL COMMENT '类型：1-邮件群发',
    `receiver`   TEXT COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT '接收者',
    `title`      VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
    `content`    TEXT COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT '内容',
    `error`      VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '错误信息',
    `status`     TINYINT(1)                              NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `migrations`
(
    `id`        INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `batch`     INT(11)                                 NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (1, '2020_08_21_145711_create_article_table', 1),
       (2, '2020_08_21_145711_create_config_table', 1),
       (3, '2020_08_21_145711_create_country_table', 1),
       (4, '2020_08_21_145711_create_coupon_log_table', 1),
       (5, '2020_08_21_145711_create_coupon_table', 1),
       (6, '2020_08_21_145711_create_email_filter_table', 1),
       (7, '2020_08_21_145711_create_failed_jobs_table', 1),
       (8, '2020_08_21_145711_create_goods_table', 1),
       (9, '2020_08_21_145711_create_invite_table', 1),
       (10, '2020_08_21_145711_create_jobs_table', 1),
       (11, '2020_08_21_145711_create_label_table', 1),
       (12, '2020_08_21_145711_create_level_table', 1),
       (13, '2020_08_21_145711_create_marketing_table', 1),
       (14, '2020_08_21_145711_create_node_auth_table', 1),
       (15, '2020_08_21_145711_create_node_certificate_table', 1),
       (16, '2020_08_21_145711_create_node_daily_data_flow_table', 1),
       (17, '2020_08_21_145711_create_node_hourly_data_flow_table', 1),
       (18, '2020_08_21_145711_create_node_label_table', 1),
       (19, '2020_08_21_145711_create_node_ping_table', 1),
       (20, '2020_08_21_145711_create_node_rule_table', 1),
       (21, '2020_08_21_145711_create_notification_log_table', 1),
       (22, '2020_08_21_145711_create_order_table', 1),
       (23, '2020_08_21_145711_create_payment_callback_table', 1),
       (24, '2020_08_21_145711_create_payment_table', 1),
       (25, '2020_08_21_145711_create_products_pool_table', 1),
       (26, '2020_08_21_145711_create_referral_apply_table', 1),
       (27, '2020_08_21_145711_create_referral_log_table', 1),
       (28, '2020_08_21_145711_create_rule_group_node_table', 1),
       (29, '2020_08_21_145711_create_rule_group_table', 1),
       (30, '2020_08_21_145711_create_rule_log_table', 1),
       (31, '2020_08_21_145711_create_rule_table', 1),
       (32, '2020_08_21_145711_create_ss_config_table', 1),
       (33, '2020_08_21_145711_create_ss_node_info_table', 1),
       (34, '2020_08_21_145711_create_ss_node_ip_table', 1),
       (35, '2020_08_21_145711_create_ss_node_online_log_table', 1),
       (36, '2020_08_21_145711_create_ss_node_table', 1),
       (37, '2020_08_21_145711_create_ticket_reply_table', 1),
       (38, '2020_08_21_145711_create_ticket_table', 1),
       (39, '2020_08_21_145711_create_user_baned_log_table', 1),
       (40, '2020_08_21_145711_create_user_credit_log_table', 1),
       (41, '2020_08_21_145711_create_user_daily_data_flow_table', 1),
       (42, '2020_08_21_145711_create_user_data_modify_log_table', 1),
       (43, '2020_08_21_145711_create_user_group_table', 1),
       (44, '2020_08_21_145711_create_user_hourly_data_flow_table', 1),
       (45, '2020_08_21_145711_create_user_login_log_table', 1),
       (46, '2020_08_21_145711_create_user_subscribe_log_table', 1),
       (47, '2020_08_21_145711_create_user_subscribe_table', 1),
       (48, '2020_08_21_145711_create_user_table', 1),
       (49, '2020_08_21_145711_create_user_traffic_log_table', 1),
       (50, '2020_08_21_145711_create_verify_code_table', 1),
       (51, '2020_08_21_145711_create_verify_table', 1),
       (52, '2020_08_21_150711_preset_data', 1);

CREATE TABLE `node_auth`
(
    `id`         INT(10) UNSIGNED                    NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED                    NOT NULL COMMENT '授权节点ID',
    `key`        CHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '认证KEY',
    `secret`     CHAR(8) COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT '通信密钥',
    `created_at` DATETIME                            NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                            NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_certificate`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `domain`     VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '域名',
    `key`        TEXT COLLATE utf8mb4_unicode_ci COMMENT '域名证书KEY',
    `pem`        TEXT COLLATE utf8mb4_unicode_ci COMMENT '域名证书PEM',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_daily_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED    NOT NULL            DEFAULT '0' COMMENT '节点ID',
    `u`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `node_daily_data_flow_node_id_index` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_hourly_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED    NOT NULL            DEFAULT '0' COMMENT '节点ID',
    `u`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `node_hourly_data_flow_node_id_index` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_label`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `label_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签ID',
    PRIMARY KEY (`id`),
    KEY `idx_node_label` (`node_id`, `label_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_ping`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '对应节点id',
    `ct`         INT(11)          NOT NULL DEFAULT '0' COMMENT '电信',
    `cu`         INT(11)          NOT NULL DEFAULT '0' COMMENT '联通',
    `cm`         INT(11)          NOT NULL DEFAULT '0' COMMENT '移动',
    `hk`         INT(11)          NOT NULL DEFAULT '0' COMMENT '香港',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `node_ping_node_id_index` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `node_rule`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED          DEFAULT NULL COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED          DEFAULT NULL COMMENT '审计规则ID',
    `is_black`   TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '是否黑名单模式：0-不是、1-是',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `notification_log`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-ServerChan、3-Bark、4-Telegram',
    `address`    VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收信地址',
    `title`      VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
    `content`    TEXT COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT '内容',
    `status`     TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '状态：-1发送失败、0-等待发送、1-发送成功',
    `error`      TEXT COLLATE utf8mb4_unicode_ci COMMENT '发送失败抛出的异常信息',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `order`
(
    `id`            INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `order_sn`      VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单编号',
    `user_id`       INT(10) UNSIGNED                       NOT NULL COMMENT '操作人',
    `goods_id`      INT(10) UNSIGNED                                DEFAULT NULL COMMENT '商品ID',
    `coupon_id`     INT(10) UNSIGNED                                DEFAULT NULL COMMENT '优惠券ID',
    `origin_amount` INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分',
    `amount`        INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '订单总价，单位分',
    `expired_at`    DATETIME                                        DEFAULT NULL COMMENT '过期时间',
    `is_expire`     TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '是否已过期：0-未过期、1-已过期',
    `pay_type`      TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal',
    `pay_way`       VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'balance' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等',
    `status`        TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成',
    `created_at`    DATETIME                               NOT NULL COMMENT '创建时间',
    `updated_at`    DATETIME                               NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_order_search` (`user_id`, `goods_id`, `is_expire`, `status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `payment`
(
    `id`         INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `trade_no`   VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付单号（本地订单号）',
    `user_id`    INT(10) UNSIGNED                       NOT NULL COMMENT '用户ID',
    `order_id`   INT(10) UNSIGNED                       NOT NULL COMMENT '本地订单ID',
    `amount`     INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '金额，单位分',
    `qr_code`    TEXT COLLATE utf8mb4_unicode_ci COMMENT '支付二维码',
    `url`        TEXT COLLATE utf8mb4_unicode_ci COMMENT '支付链接',
    `status`     TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '支付状态：-1-支付失败、0-等待支付、1-支付成功',
    `created_at` DATETIME                               NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                               NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `payment_callback`
(
    `id`           INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `trade_no`     VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '本地订单号',
    `out_trade_no` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外部订单号（支付平台）',
    `amount`       INT(10) UNSIGNED                       NOT NULL COMMENT '交易金额，单位分',
    `status`       TINYINT(1)                             NOT NULL COMMENT '交易状态：0-失败、1-成功',
    `created_at`   DATETIME                               NOT NULL COMMENT '创建时间',
    `updated_at`   DATETIME                               NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `products_pool`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `min_amount` INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '适用最小金额，单位分',
    `max_amount` INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '适用最大金额，单位分',
    `status`     TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '状态：0-未启用、1-已启用',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `referral_apply`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `before`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作前可提现金额，单位分',
    `after`      INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作后可提现金额，单位分',
    `amount`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本次提现金额，单位分',
    `link_logs`  JSON             NOT NULL COMMENT '关联返利日志ID，例如：1,3,4',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `referral_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `invitee_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `inviter_id` INT(10) UNSIGNED NOT NULL COMMENT '推广人ID',
    `order_id`   INT(10) UNSIGNED NOT NULL COMMENT '关联订单ID',
    `amount`     INT(10) UNSIGNED NOT NULL COMMENT '消费金额，单位分',
    `commission` INT(10) UNSIGNED NOT NULL COMMENT '返利金额',
    `status`     TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未提现、1-审核中、2-已提现',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `rule`
(
    `id`      INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`    TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '类型：1-正则表达式、2-域名、3-IP、4-协议',
    `name`    VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则描述',
    `pattern` TEXT COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT '规则值',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `rule` (`id`, `type`, `name`, `pattern`)
VALUES (1, 1, '360', '(.*.||)(^360|0360|1360|3600|360safe|^so|qhimg|qhmsg|^yunpan|qihoo|qhcdn|qhupdate|360totalsecurity|360shouji|qihucdn|360kan|secmp).(cn|com|net)'),
       (2, 1, '腾讯管家', '(.guanjia.qq.com|qqpcmgr|QQPCMGR)'),
       (3, 1, '金山毒霸', '(.*.||)(rising|kingsoft|duba|xindubawukong|jinshanduba).(com|net|org)'),
       (4, 1, '暗网相关', '(.*.||)(netvigator|torproject).(cn|com|net|org)'),
       (5, 1, '百度定位', '(api|ps|sv|offnavi|newvector|ulog.imap|newloc|tracknavi)(.map|).(baidu|n.shifen).com'),
       (6, 1, '法轮功类', '(.*.||)(dafahao|minghui|dongtaiwang|dajiyuan|falundata|shenyun|tuidang|epochweekly|epochtimes|ntdtv|falundafa|wujieliulan|zhengjian).(org|com|net)'),
       (7, 1, 'BT扩展名', '(torrent|.torrent|peer_id=|info_hash|get_peers|find_node|BitTorrent|announce_peer|announce.php?passkey=)'),
       (8, 1, '邮件滥发', '((^.*@)(guerrillamail|guerrillamailblock|sharklasers|grr|pokemail|spam4|bccto|chacuo|027168).(info|biz|com|de|net|org|me|la)|Subject|HELO|SMTP)'),
       (9, 1, '迅雷下载', '(.?)(xunlei|sandai|Thunder|XLLiveUD)(.)'),
       (10, 1, '大陆应用', '(.*.||)(baidu|qq|163|189|10000|10010|10086|sohu|sogoucdn|sogou|uc|58|taobao|qpic|bilibili|hdslb|acgvideo|sina|douban|doubanio|xiaohongshu|sinaimg|weibo|xiaomi|youzanyun|meituan|dianping|biliapi|huawei|pinduoduo|cnzz).(org|com|net|cn)'),
       (11, 1, '大陆银行', '(.*.||)(icbc|ccb|boc|bankcomm|abchina|cmbchina|psbc|cebbank|cmbc|pingan|spdb|citicbank|cib|hxb|bankofbeijing|hsbank|tccb|4001961200|bosc|hkbchina|njcb|nbcb|lj-bank|bjrcb|jsbchina|gzcb|cqcbank|czbank|hzbank|srcb|cbhb|cqrcb|grcbank|qdccb|bocd|hrbcb|jlbank|bankofdl|qlbchina|dongguanbank|cscb|hebbank|drcbank|zzbank|bsb|xmccb|hljrcc|jxnxs|gsrcu|fjnx|sxnxs|gx966888|gx966888|zj96596|hnnxs|ahrcu|shanxinj|hainanbank|scrcu|gdrcu|hbxh|ynrcc|lnrcc|nmgnxs|hebnx|jlnls|js96008|hnnx|sdnxs).(org|com|net|cn)'),
       (12, 1, '台湾银行', '(.*.||)(firstbank|bot|cotabank|megabank|tcb-bank|landbank|hncb|bankchb|tbb|ktb|tcbbank|scsb|bop|sunnybank|kgibank|fubon|ctbcbank|cathaybk|eximbank|bok|ubot|feib|yuantabank|sinopac|esunbank|taishinbank|jihsunbank|entiebank|hwataibank|csc|skbank).(org|com|net|tw)'),
       (13, 1, '大陆第三方支付', '(.*.||)(alipay|baifubao|yeepay|99bill|95516|51credit|cmpay|tenpay|lakala|jdpay).(org|com|net|cn)'),
       (14, 1, '台湾特供', '(.*.||)(visa|mycard|mastercard|gov|gash|beanfun|bank|line).(org|com|net|cn|tw|jp|kr)'),
       (15, 1, '涉政治类', '(.*.||)(shenzhoufilm|secretchina|renminbao|aboluowang|mhradio|guangming|zhengwunet|soundofhope|yuanming|zhuichaguoji|fgmtv|xinsheng|shenyunperformingarts|epochweekly|tuidang|shenyun|falundata|bannedbook|pincong|rfi|mingjingnews|boxun|rfa|scmp|ogate|voachinese).(org|com|net|rocks|fr)'),
       (16, 1, '流媒体', '(.*.||)(youtube|googlevideo|hulu|netflix|nflxvideo|akamai|nflximg|hbo|mtv|bbc|tvb).(org|club|com|net|tv)'),
       (17, 1, '测速类', '(.*.||)(fast|speedtest).(org|com|net|cn)'),
       (18, 1, '外汇交易类', '(.*.||)(metatrader4|metatrader5|mql5).(org|com|net)');

CREATE TABLE `rule_group`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '模式：1-阻断、0-放行',
    `name`       VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
    `rules`      JSON                                             DEFAULT NULL COMMENT '关联的规则ID，多个用,号分隔',
    `nodes`      JSON                                             DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `rule_group_node`
(
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_group_id` INT(10) UNSIGNED NOT NULL COMMENT '规则分组ID',
    `node_id`       INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    `created_at`    DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`    DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `rule_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址',
    `reason`     VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '触发原因',
    `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx` (`user_id`, `node_id`, `rule_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ss_config`
(
    `id`         INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名',
    `type`       TINYINT(1)                             NOT NULL DEFAULT '1' COMMENT '类型：1-加密方式、2-协议、3-混淆',
    `is_default` TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '是否默认：0-不是、1-是',
    `sort`       TINYINT(3) UNSIGNED                    NOT NULL DEFAULT '0' COMMENT '排序：值越大排越前',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `ss_config` (`id`, `name`, `type`, `is_default`, `sort`)
VALUES (1, 'none', 1, 1, 0),
       (2, 'rc4-md5', 1, 0, 0),
       (3, 'aes-128-cfb', 1, 0, 0),
       (4, 'aes-192-cfb', 1, 0, 0),
       (5, 'aes-256-cfb', 1, 0, 0),
       (6, 'aes-128-ctr', 1, 0, 0),
       (7, 'aes-192-ctr', 1, 0, 0),
       (8, 'aes-256-ctr', 1, 0, 0),
       (9, 'aes-128-gcm', 1, 0, 0),
       (10, 'aes-192-gcm', 1, 0, 0),
       (11, 'aes-256-gcm', 1, 0, 0),
       (12, 'bf-cfb', 1, 0, 0),
       (13, 'cast5-cfb', 1, 0, 0),
       (14, 'des-cfb', 1, 0, 0),
       (15, 'salsa20', 1, 0, 0),
       (16, 'chacha20', 1, 0, 0),
       (17, 'chacha20-ietf', 1, 0, 0),
       (18, 'chacha20-ietf-poly1305', 1, 0, 0),
       (19, 'origin', 2, 1, 0),
       (20, 'auth_sha1_v4', 2, 0, 0),
       (21, 'auth_aes128_md5', 2, 0, 0),
       (22, 'auth_aes128_sha1', 2, 0, 0),
       (23, 'auth_chain_a', 2, 0, 0),
       (24, 'auth_chain_b', 2, 0, 0),
       (25, 'auth_chain_c', 2, 0, 0),
       (26, 'auth_chain_d', 2, 0, 0),
       (27, 'auth_chain_e', 2, 0, 0),
       (28, 'auth_chain_f', 2, 0, 0),
       (29, 'plain', 3, 1, 0),
       (30, 'http_simple', 3, 0, 0),
       (31, 'http_post', 3, 0, 0),
       (32, 'tls1.2_ticket_auth', 3, 0, 0),
       (33, 'tls1.2_ticket_fastauth', 3, 0, 0);

CREATE TABLE `ss_node`
(
    `id`             INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `type`           TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet',
    `name`           VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `country_code`   CHAR(5) COLLATE utf8mb4_unicode_ci      NOT NULL DEFAULT 'un' COMMENT '国家代码',
    `server`         VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '服务器域名地址',
    `ip`             VARCHAR(45) COLLATE utf8mb4_unicode_ci           DEFAULT NULL COMMENT '服务器IPV4地址',
    `ipv6`           VARCHAR(45) COLLATE utf8mb4_unicode_ci           DEFAULT NULL COMMENT '服务器IPV6地址',
    `level`          TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '等级：0-无等级，全部可见',
    `speed_limit`    BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '节点限速，为0表示不限速，单位Byte',
    `client_limit`   SMALLINT(5) UNSIGNED                    NOT NULL DEFAULT '0' COMMENT '设备数限制',
    `relay_server`   VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '中转地址',
    `relay_port`     SMALLINT(5) UNSIGNED                             DEFAULT NULL COMMENT '中转端口',
    `description`    VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '节点简单描述',
    `geo`            VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '节点地理位置',
    `method`         VARCHAR(32) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
    `protocol`       VARCHAR(64) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'origin' COMMENT '协议',
    `protocol_param` VARCHAR(128) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '协议参数',
    `obfs`           VARCHAR(64) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'plain' COMMENT '混淆',
    `obfs_param`     VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '混淆参数',
    `traffic_rate`   DOUBLE(6, 2) UNSIGNED                   NOT NULL DEFAULT '1.00' COMMENT '流量比率',
    `is_subscribe`   TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '是否允许用户订阅该节点：0-否、1-是',
    `is_ddns`        TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '是否使用DDNS：0-否、1-是',
    `is_relay`       TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '是否中转节点：0-否、1-是',
    `is_udp`         TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '是否启用UDP：0-不启用、1-启用',
    `push_port`      SMALLINT(5) UNSIGNED                    NOT NULL DEFAULT '1000' COMMENT '消息推送端口',
    `detection_type` TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部',
    `compatible`     TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '兼容SS',
    `single`         TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '启用单端口功能：0-否、1-是',
    `port`           SMALLINT(5) UNSIGNED                             DEFAULT NULL COMMENT '单端口的端口号或连接端口号',
    `passwd`         VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '单端口的连接密码',
    `sort`           TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠前显示',
    `status`         TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '状态：0-维护、1-正常',
    `v2_alter_id`    SMALLINT(5) UNSIGNED                    NOT NULL DEFAULT '16' COMMENT 'V2Ray额外ID',
    `v2_port`        SMALLINT(5) UNSIGNED                    NOT NULL DEFAULT '0' COMMENT 'V2Ray服务端口',
    `v2_method`      VARCHAR(32) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'aes-128-gcm' COMMENT 'V2Ray加密方式',
    `v2_net`         VARCHAR(16) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'tcp' COMMENT 'V2Ray传输协议',
    `v2_type`        VARCHAR(32) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'none' COMMENT 'V2Ray伪装类型',
    `v2_host`        VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT 'V2Ray伪装的域名',
    `v2_path`        VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT 'V2Ray的WS/H2路径',
    `v2_tls`         TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT 'V2Ray连接TLS：0-未开启、1-开启',
    `tls_provider`   TEXT COLLATE utf8mb4_unicode_ci COMMENT 'V2Ray节点的TLS提供商授权信息',
    `created_at`     DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at`     DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    KEY `ss_node_is_subscribe_index` (`is_subscribe`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ss_node_info`
(
    `id`       INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `node_id`  INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '节点ID',
    `uptime`   INT(10) UNSIGNED                        NOT NULL COMMENT '后端存活时长，单位秒',
    `load`     VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '负载',
    `log_time` INT(10) UNSIGNED                        NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    KEY `ss_node_info_node_id_index` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ss_node_ip`
(
    `id`         INT(10) UNSIGNED                   NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED                   NOT NULL DEFAULT '0' COMMENT '节点ID',
    `user_id`    INT(10) UNSIGNED                   NOT NULL DEFAULT '0' COMMENT '用户ID',
    `port`       SMALLINT(5) UNSIGNED               NOT NULL DEFAULT '0' COMMENT '端口',
    `type`       CHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tcp' COMMENT '类型：all、tcp、udp',
    `ip`         TEXT COLLATE utf8mb4_unicode_ci COMMENT '连接IP：每个IP用,号隔开',
    `created_at` INT(10) UNSIGNED                   NOT NULL DEFAULT '0' COMMENT '上报时间',
    PRIMARY KEY (`id`),
    KEY `ss_node_ip_node_id_index` (`node_id`),
    KEY `ss_node_ip_user_id_index` (`user_id`),
    KEY `ss_node_ip_port_index` (`port`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ss_node_online_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`     INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    `online_user` INT(10) UNSIGNED NOT NULL COMMENT '在线用户数',
    `log_time`    INT(10) UNSIGNED NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    KEY `ss_node_online_log_node_id_index` (`node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ticket`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '用户ID',
    `admin_id`   INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `title`      VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
    `content`    TEXT COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT '内容',
    `status`     TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '状态：0-待处理、1-已处理未关闭、2-已关闭',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `ticket_reply`
(
    `id`         INT(10) UNSIGNED                NOT NULL AUTO_INCREMENT,
    `ticket_id`  INT(10) UNSIGNED                NOT NULL COMMENT '工单ID',
    `user_id`    INT(10) UNSIGNED                NOT NULL DEFAULT '0' COMMENT '回复用户ID',
    `admin_id`   INT(10) UNSIGNED                NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `content`    TEXT COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回复内容',
    `created_at` DATETIME                        NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                        NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user`
(
    `id`              INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `username`        VARCHAR(64) COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT '昵称',
    `email`           VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
    `password`        VARCHAR(64) COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT '密码',
    `port`            SMALLINT(5) UNSIGNED                    NOT NULL DEFAULT '0' COMMENT '代理端口',
    `passwd`          VARCHAR(16) COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT '代理密码',
    `vmess_id`        CHAR(36) COLLATE utf8mb4_unicode_ci     NOT NULL,
    `transfer_enable` BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '1099511627776' COMMENT '可用流量，单位字节，默认1TiB',
    `u`               BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '已上传流量，单位字节',
    `d`               BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '已下载流量，单位字节',
    `t`               INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '最后使用时间',
    `ip`              VARCHAR(45) COLLATE utf8mb4_unicode_ci           DEFAULT NULL COMMENT '最后连接IP',
    `enable`          TINYINT(1)                              NOT NULL DEFAULT '1' COMMENT '代理状态',
    `method`          VARCHAR(30) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
    `protocol`        VARCHAR(30) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'origin' COMMENT '协议',
    `protocol_param`  VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT '协议参数',
    `obfs`            VARCHAR(30) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT 'plain' COMMENT '混淆',
    `speed_limit`     BIGINT(20) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '用户限速，为0表示不限速，单位Byte',
    `wechat`          VARCHAR(30) COLLATE utf8mb4_unicode_ci           DEFAULT NULL COMMENT '微信',
    `qq`              VARCHAR(20) COLLATE utf8mb4_unicode_ci           DEFAULT NULL COMMENT 'QQ',
    `credit`          INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '余额，单位分',
    `expired_at`      DATE                                    NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
    `ban_time`        INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '封禁到期时间',
    `remark`          TEXT COLLATE utf8mb4_unicode_ci COMMENT '备注',
    `level`           TINYINT(3) UNSIGNED                     NOT NULL DEFAULT '0' COMMENT '等级，默认0级',
    `group_id`        INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '所属分组',
    `is_admin`        TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '是否管理员：0-否、1-是',
    `reg_ip`          VARCHAR(45) COLLATE utf8mb4_unicode_ci  NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
    `last_login`      INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '最后登录时间',
    `inviter_id`      INT(10) UNSIGNED                                 DEFAULT NULL COMMENT '邀请人',
    `reset_time`      DATE                                             DEFAULT NULL COMMENT '流量重置日期',
    `invite_num`      INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '可生成邀请码数',
    `status`          TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '状态：-1-禁用、0-未激活、1-正常',
    `remember_token`  VARCHAR(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL,
    `created_at`      DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at`      DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_email_unique` (`email`),
    KEY `idx_search` (`enable`, `status`, `port`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `user` (`id`, `username`, `email`, `password`, `port`, `passwd`, `vmess_id`, `transfer_enable`, `u`, `d`, `t`, `ip`, `enable`, `method`, `protocol`, `protocol_param`, `obfs`, `speed_limit`, `wechat`, `qq`, `credit`, `expired_at`, `ban_time`, `remark`, `level`, `group_id`, `is_admin`, `reg_ip`, `last_login`, `inviter_id`, `reset_time`, `invite_num`, `status`, `remember_token`, `created_at`, `updated_at`)
VALUES (1, '管理员', 'test@test.com', '$2y$10$vDaFh91Fn5vjdG1M5grp6OHwKNf7jEGo47794.5GTC7H5sEvNah6e', 10000, '32uNUkMfikhi5twv', '0a9f2656-395b-4ecf-8134-c8462d245156', 1099511627776, 0, 0, NULL, NULL, 1, 'aes-256-cfb', 'origin', NULL, 'plain', 0, NULL, NULL, 0, '2099-01-01', NULL, NULL, 0, 0, 0, '127.0.0.1', 0, NULL, NULL, 0, 0, NULL, '2020-08-25 13:09:16', '2020-08-25 13:09:16');

CREATE TABLE `user_baned_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `time`        INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '封禁账号时长，单位分钟',
    `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作描述',
    `status`      TINYINT(1)       NOT NULL               DEFAULT '0' COMMENT '状态：0-未处理、1-已处理',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    `updated_at`  DATETIME         NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_credit_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '账号ID',
    `order_id`    INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '订单ID',
    `before`      INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '发生前余额，单位分',
    `after`       INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '发生后金额，单位分',
    `amount`      INT(11)          NOT NULL               DEFAULT '0' COMMENT '发生金额，单位分',
    `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作描述',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_daily_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED    NOT NULL            DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED    NOT NULL            DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    `u`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_node` (`user_id`, `node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_data_modify_log`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '用户ID',
    `order_id`    INT(10) UNSIGNED NOT NULL               DEFAULT '0' COMMENT '发生的订单ID',
    `before`      BIGINT(20)       NOT NULL               DEFAULT '0' COMMENT '操作前流量',
    `after`       BIGINT(20)       NOT NULL               DEFAULT '0' COMMENT '操作后流量',
    `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
    `created_at`  DATETIME         NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_group`
(
    `id`    INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
    `nodes` JSON DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_hourly_data_flow`
(
    `id`         INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED    NOT NULL COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED    NOT NULL            DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    `u`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '上传流量',
    `d`          BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '下载流量',
    `total`      BIGINT(20) UNSIGNED NOT NULL            DEFAULT '0' COMMENT '总流量',
    `traffic`    VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
    `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_node` (`user_id`, `node_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_login_log`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED                        NOT NULL DEFAULT '0' COMMENT '用户ID',
    `ip`         VARCHAR(45) COLLATE utf8mb4_unicode_ci  NOT NULL COMMENT 'IP地址',
    `country`    VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '国家',
    `province`   VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '省份',
    `city`       VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '城市',
    `county`     VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '郡县',
    `isp`        VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '运营商',
    `area`       VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '地区',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_subscribe`
(
    `id`         INT(10) UNSIGNED                   NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED                   NOT NULL DEFAULT '0' COMMENT '用户ID',
    `code`       CHAR(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订阅地址唯一识别码',
    `times`      INT(10) UNSIGNED                   NOT NULL DEFAULT '0' COMMENT '地址请求次数',
    `status`     TINYINT(1)                         NOT NULL DEFAULT '1' COMMENT '状态：0-禁用、1-启用',
    `ban_time`   INT(10) UNSIGNED                            DEFAULT NULL COMMENT '封禁时间',
    `ban_desc`   VARCHAR(50) COLLATE utf8mb4_unicode_ci      DEFAULT NULL COMMENT '封禁理由',
    `created_at` DATETIME                           NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                           NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`, `status`),
    KEY `user_subscribe_code_index` (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `user_subscribe` (`id`, `user_id`, `code`, `times`, `status`, `ban_time`, `ban_desc`, `created_at`, `updated_at`)
VALUES (1, 1, 'SVgMC2Wx', 0, 1, NULL, NULL, '2020-08-25 13:09:16', '2020-08-25 13:09:16');

CREATE TABLE `user_subscribe_log`
(
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_subscribe_id` INT(10) UNSIGNED NOT NULL COMMENT '对应user_subscribe的id',
    `request_ip`        VARCHAR(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '请求IP',
    `request_time`      DATETIME         NOT NULL COMMENT '请求时间',
    `request_header`    TEXT COLLATE utf8mb4_unicode_ci COMMENT '请求头部信息',
    PRIMARY KEY (`id`),
    KEY `user_subscribe_log_user_subscribe_id_index` (`user_subscribe_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `user_traffic_log`
(
    `id`       INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `user_id`  INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`  INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '节点ID',
    `u`        INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '上传流量',
    `d`        INT(10) UNSIGNED                       NOT NULL DEFAULT '0' COMMENT '下载流量',
    `rate`     DOUBLE(6, 2) UNSIGNED                  NOT NULL COMMENT '倍率',
    `traffic`  VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '产生流量',
    `log_time` INT(10) UNSIGNED                       NOT NULL COMMENT '记录时间',
    PRIMARY KEY (`id`),
    KEY `idx_user_node_time` (`user_id`, `node_id`, `log_time`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `verify`
(
    `id`         INT(10) UNSIGNED                       NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)                             NOT NULL DEFAULT '1' COMMENT '激活类型：1-自行激活、2-管理员激活',
    `user_id`    INT(10) UNSIGNED                       NOT NULL COMMENT '用户ID',
    `token`      VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '校验token',
    `status`     TINYINT(1)                             NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at` DATETIME                               NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                               NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `verify_code`
(
    `id`         INT(10) UNSIGNED                        NOT NULL AUTO_INCREMENT,
    `address`    VARCHAR(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户邮箱',
    `code`       CHAR(6) COLLATE utf8mb4_unicode_ci      NOT NULL COMMENT '验证码',
    `status`     TINYINT(1)                              NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at` DATETIME                                NOT NULL COMMENT '创建时间',
    `updated_at` DATETIME                                NOT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;