-- 由于本次数据库改动过大，请在更新前备份数据库！
-- 由于本次数据库改动过大，请在更新前备份数据库！
-- 由于本次数据库改动过大，请在更新前备份数据库！
-- 本次对数据库数据类型进行规范化处理，会修改很多字段；
-- 添加web api需要字段；
ALTER TABLE `ss_node`
    CHANGE `id` `id`                         INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type`                     TINYINT(1) UNSIGNED  NOT NULL DEFAULT '1' COMMENT '服务类型：1-ShadowsocksR、2-V2ray',
    DROP `group_id`,
    DROP INDEX `idx_group`,
    CHANGE `server` `server`                 VARCHAR(255)         NULL     DEFAULT '' COMMENT '服务器域名地址',
    CHANGE `ip` `ip`                         CHAR(15)             NULL     DEFAULT NULL COMMENT '服务器IPV4地址',
    CHANGE `ipv6` `ipv6`                     VARCHAR(128)         NULL     DEFAULT NULL COMMENT '服务器IPV6地址',
    ADD `relay_server`                       VARCHAR(255)         NULL     DEFAULT NULL COMMENT '中转地址' AFTER `ipv6`,
    ADD `relay_port`                         SMALLINT(5) UNSIGNED NULL     DEFAULT 0 COMMENT '中转端口' AFTER `relay_server`,
    ADD `level`                              TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '等级：0-无等级，全部可见' AFTER `relay_port`,
    ADD `speed_limit`                        BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '节点限速，为0表示不限速，单位Byte' AFTER `level`,
    ADD `client_limit`                       TINYINT(3) UNSIGNED  NOT NULL DEFAULT 0 COMMENT '设备数限制' AFTER `speed_limit`,
    CHANGE `desc` `description`              VARCHAR(255)         NULL     DEFAULT '' COMMENT '节点简单描述',
    CHANGE `protocol_param` `protocol_param` VARCHAR(128)         NULL     DEFAULT NULL COMMENT '协议参数',
    CHANGE `obfs_param` `obfs_param`         VARCHAR(255)         NULL     DEFAULT NULL COMMENT '混淆参数',
    CHANGE `traffic_rate` `traffic_rate`     FLOAT(6, 2) UNSIGNED NOT NULL DEFAULT '1.00' COMMENT '流量比率',
    DROP `bandwidth`,
    DROP `traffic`,
    DROP `monitor_url`,
    CHANGE `is_subscribe` `is_subscribe`     BIT                  NOT NULL DEFAULT 1 COMMENT '是否允许用户订阅该节点：0-否、1-是',
    CHANGE `is_ddns` `is_ddns`               BIT                  NOT NULL DEFAULT 0 COMMENT '是否使用DDNS：0-否、1-是',
    CHANGE `is_transit` `is_relay`           BIT                  NOT NULL DEFAULT 0 COMMENT '是否中转节点：0-否、1-是',
    ADD `is_udp`                             BIT                  NOT NULL DEFAULT 1 COMMENT '是否启用UDP：0-不启用、1-启用' AFTER `is_relay`,
    CHANGE `ssh_port` `ssh_port`             SMALLINT(5) UNSIGNED NOT NULL DEFAULT '22' COMMENT 'SSH端口',
    CHANGE `detectiontype` `detection_type`  TINYINT(1)           NOT NULL DEFAULT '1' COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部',
    CHANGE `compatible` `compatible`         BIT                  NOT NULL DEFAULT 0 COMMENT '兼容SS',
    CHANGE `single` `single`                 BIT                  NOT NULL DEFAULT 0 COMMENT '启用单端口功能：0-否、1-是',
    CHANGE `port` `port`                     SMALLINT(5) UNSIGNED NULL     DEFAULT NULL COMMENT '单端口的端口号或连接端口号',
    CHANGE `passwd` `passwd`                 VARCHAR(255)         NULL     DEFAULT NULL COMMENT '单端口的连接密码',
    CHANGE `sort` `sort`                     TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠前显示',
    CHANGE `status` `status`                 BIT                  NOT NULL DEFAULT 1 COMMENT '状态：0-维护、1-正常',
    CHANGE `v2_alter_id` `v2_alter_id`       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '16' COMMENT 'V2Ray额外ID',
    CHANGE `v2_port` `v2_port`               SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'V2Ray服务端口',
    CHANGE `v2_method` `v2_method`           VARCHAR(32)          NOT NULL DEFAULT 'aes-128-gcm' COMMENT 'V2Ray加密方式',
    CHANGE `v2_net` `v2_net`                 VARCHAR(16)          NOT NULL DEFAULT 'tcp' COMMENT 'V2Ray传输协议',
    CHANGE `v2_type` `v2_type`               VARCHAR(32)          NOT NULL DEFAULT 'none' COMMENT 'V2Ray伪装类型',
    CHANGE `v2_host` `v2_host`               VARCHAR(255)         NOT NULL DEFAULT '' COMMENT 'V2Ray伪装的域名',
    CHANGE `v2_path` `v2_path`               VARCHAR(255)         NOT NULL DEFAULT '' COMMENT 'V2Ray的WS/H2路径',
    CHANGE `v2_tls` `v2_tls`                 BIT                  NOT NULL DEFAULT 0 COMMENT 'V2Ray连接TLS：0-未开启、1-开启',
    DROP `v2_insider_port`,
    DROP `v2_outsider_port`,
    ADD `v2_tls_insecure`                    BIT                  NOT NULL DEFAULT 0 COMMENT '是否允许不安全连接' AFTER `v2_tls`,
    ADD `v2_tls_insecure_ciphers`            BIT                  NOT NULL DEFAULT 0 COMMENT '是否允许不安全的加密方式' AFTER `v2_tls_insecure`;

DROP TABLE IF EXISTS `ss_node_deny`;

ALTER TABLE `ss_node_info`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `uptime` `uptime`     INT(10) UNSIGNED NOT NULL COMMENT '后端存活时长，单位秒',
    CHANGE `log_time` `log_time` INT(10) UNSIGNED NOT NULL COMMENT '记录时间';

ALTER TABLE `ss_node_online_log`
    CHANGE `id` `id`                   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id`         INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    CHANGE `online_user` `online_user` INT(10) UNSIGNED NOT NULL COMMENT '在线用户数',
    CHANGE `log_time` `log_time`       INT(10) UNSIGNED NOT NULL COMMENT '记录时间';

ALTER TABLE `ss_node_ping`
    CHANGE `id` `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '对应节点id',
    CHANGE `ct` `ct`           INT(10)          NOT NULL DEFAULT '0' COMMENT '电信',
    CHANGE `cu` `cu`           INT(10)          NOT NULL DEFAULT '0' COMMENT '联通',
    CHANGE `cm` `cm`           INT(10)          NOT NULL DEFAULT '0' COMMENT '移动',
    CHANGE `hk` `hk`           INT(10)          NOT NULL DEFAULT '0' COMMENT '香港';

ALTER TABLE `ss_node_label`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `label_id` `label_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签ID';

UPDATE `user` SET `transfer_enable` = 0 WHERE `transfer_enable` < 0;
ALTER TABLE `user`
    CHANGE `id` `id`                           INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    CHANGE `username` `username`               VARCHAR(64)          NOT NULL DEFAULT '' COMMENT '昵称',
    CHANGE `email` `email`                     VARCHAR(128)         NOT NULL DEFAULT '' COMMENT '邮箱',
    CHANGE `port` `port`                       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '代理端口',
    CHANGE `vmess_id` `uuid`                   VARCHAR(64)          NOT NULL DEFAULT '',
    CHANGE `transfer_enable` `transfer_enable` BIGINT(20) UNSIGNED  NOT NULL DEFAULT '1099511627776' COMMENT '可用流量，单位字节，默认1TiB',
    CHANGE `u` `u`                             BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '已上传流量，单位字节',
    CHANGE `d` `d`                             BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '已下载流量，单位字节',
    CHANGE `t` `t`                             INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '最后使用时间',
    DROP `protocol_param`,
    DROP `obfs_param`,
    DROP `speed_limit_per_con`,
    DROP `speed_limit_per_user`,
    ADD `speed_limit`                          BIGINT(20) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '用户限速，为0表示不限速，单位Byte' AFTER `obfs`,
    DROP `usage`,
    DROP `pay_way`,
    CHANGE `balance` `credit`                  INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '余额，单位分',
    CHANGE `ban_time` `ban_time`               INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '封禁到期时间',
    CHANGE `level` `level`                     TINYINT(3) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '等级，默认0级',
    CHANGE `is_admin` `is_admin`               BIT                  NOT NULL DEFAULT 0 COMMENT '是否管理员：0-否、1-是',
    CHANGE `reg_ip` `reg_ip`                   CHAR(15)             NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
    CHANGE `last_login` `last_login`           INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '最后登录时间',
    CHANGE `referral_uid` `referral_uid`       INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '邀请人',
    CHANGE `reset_time` `reset_time`           DATE                 NULL COMMENT '流量重置日期，NULL表示不重置',
    CHANGE `invite_num` `invite_num`           INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '可生成邀请码数',
    CHANGE `remember_token` `remember_token`   VARCHAR(255) DEFAULT '';

DROP TABLE IF EXISTS `level`;
CREATE TABLE `level`
(
    `id`    INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '等级',
    `name`  VARCHAR(100)        NOT NULL DEFAULT '' COMMENT '等级名称',
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


ALTER TABLE `user_traffic_log`
    CHANGE `id` `id`             INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`   INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `node_id` `node_id`   INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `u` `u`               INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '上传流量',
    CHANGE `d` `d`               INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '下载流量',
    CHANGE `rate` `rate`         FLOAT(6, 2) UNSIGNED NOT NULL COMMENT '倍率',
    CHANGE `log_time` `log_time` INT(10) UNSIGNED     NOT NULL COMMENT '记录时间';

ALTER TABLE `ss_config`
    CHANGE `id` `id`                 INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type`             TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '类型：1-加密方式、2-协议、3-混淆',
    CHANGE `is_default` `is_default` BIT                 NOT NULL DEFAULT 0 COMMENT '是否默认：0-不是、1-是',
    CHANGE `sort` `sort`             TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序：值越大排越前';

ALTER TABLE `config`
    CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `article`
    CHANGE `id` `id`     INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type` TINYINT(1) DEFAULT '1' COMMENT '类型：1-文章、2-站内公告、3-站外公告',
    CHANGE `sort` `sort` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序';

ALTER TABLE `invite`
    CHANGE `id` `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `uid` `uid`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '邀请人ID',
    CHANGE `fuid` `fuid`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '受邀人ID',
    CHANGE `status` `status` TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '邀请码状态：0-未使用、1-已使用、2-已过期';

DROP TABLE `label`;

CREATE TABLE `label`
(
    `id`   INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255)        NOT NULL DEFAULT '' COMMENT '名称',
    `sort` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序值',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='标签';

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

ALTER TABLE `verify`
    CHANGE `id` `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type`       TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '激活类型：1-自行激活、2-管理员激活',
    CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID';

ALTER TABLE `verify_code`
    CHANGE `id` `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `status` `status` TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效';

DROP TABLE `ss_group`;
DROP TABLE `ss_group_node`;

ALTER TABLE `goods`
    CHANGE `id` `id`                 INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    DROP `sku`,
    CHANGE `logo` `logo`             VARCHAR(255) DEFAULT NULL COMMENT '商品图片地址',
    CHANGE `traffic` `traffic`       BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品内含多少流量，单位MiB',
    CHANGE `type` `type`             TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '商品类型：1-流量包、2-套餐',
    CHANGE `price` `price`           INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '售价，单位分',
    ADD `level`                      TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买后给用户授权的等级' AFTER `price`,
    CHANGE `renew` `renew`           INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '流量重置价格，单位分',
    CHANGE `period` `period`         INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '流量自动重置周期',
    CHANGE `desc` `description`      VARCHAR(255) DEFAULT '' COMMENT '商品描述',
    CHANGE `days` `days`             INT(10) UNSIGNED    NOT NULL DEFAULT '30' COMMENT '有效期',
    CHANGE `invite_num` `invite_num` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '赠送邀请码数',
    CHANGE `limit_num` `limit_num`   INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '限购数量，默认为0不限购',
    CHANGE `sort` `sort`             TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
    CHANGE `is_hot` `is_hot`         BIT                 NOT NULL DEFAULT 0 COMMENT '是否热销：0-否、1-是',
    CHANGE `status` `status`         BIT                 NOT NULL DEFAULT 1 COMMENT '状态：0-下架、1-上架';

DROP TABLE IF EXISTS `coupon`;
CREATE TABLE `coupon`
(
    `id`              INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(50)         NOT NULL COMMENT '优惠券名称',
    `logo`            VARCHAR(255)        NOT NULL DEFAULT '' COMMENT '优惠券LOGO',
    `sn`              VARCHAR(50)         NOT NULL DEFAULT '' COMMENT '优惠券码',
    `type`            TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '类型：1-抵用券、2-折扣券、3-充值券',
    `usage_count`     SMALLINT UNSIGNED   NOT NULL DEFAULT '1' COMMENT '可使用次数',
    `amount`          INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '金额，单位分',
    `discount`        DECIMAL(10, 2)      NOT NULL DEFAULT '0.00' COMMENT '折扣',
    `rule`            INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '使用限制，单位分',
    `available_start` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '有效期开始',
    `available_end`   INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '有效期结束',
    `status`          TINYINT(1)          NOT NULL DEFAULT '1' COMMENT '状态：0-未使用、1-已使用、2-已失效',
    `created_at`      DATETIME                     DEFAULT NULL COMMENT '创建时间',
    `updated_at`      DATETIME                     DEFAULT NULL COMMENT '最后更新时间',
    `deleted_at`      DATETIME            NULL     DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unq_sn` (`sn`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='优惠券';

ALTER TABLE `coupon_log`
    CHANGE `id` `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `coupon_id` `coupon_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠券ID',
    CHANGE `goods_id` `goods_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
    CHANGE `order_id` `order_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
    CHANGE `desc` `description`    VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '备注';

CREATE TABLE `products_pool`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255)              DEFAULT NULL COMMENT '名称',
    `min_amount` INT(10) UNSIGNED          DEFAULT 0 COMMENT '适用最小金额，单位分',
    `max_amount` INT(10) UNSIGNED          DEFAULT 0 COMMENT '适用最大金额，单位分',
    `status`     BIT              NOT NULL DEFAULT 1 COMMENT '状态：0-未启用、1-已启用',
    `created_at` DATETIME                  DEFAULT NULL COMMENT '创建时间',
    `updated_at` DATETIME                  DEFAULT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='产品名称池';

UPDATE `order` SET `goods_id` = 0 WHERE `goods_id` = -1;
ALTER TABLE `order`
    CHANGE `oid` `oid`                     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`             INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人',
    CHANGE `goods_id` `goods_id`           INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品ID',
    CHANGE `coupon_id` `coupon_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠券ID',
    DROP `email`,
    CHANGE `origin_amount` `origin_amount` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分',
    CHANGE `amount` `amount`               INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单总价，单位分',
    CHANGE `is_expire` `is_expire`         BIT              NOT NULL DEFAULT 0 COMMENT '是否已过期：0-未过期、1-已过期',
    CHANGE `pay_way` `pay_way`             VARCHAR(10)      NOT NULL DEFAULT '' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等',
    CHANGE `status` `status`               TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成';

ALTER TABLE `ticket`
    CHANGE `id` `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `status` `status`   TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-待处理、1-已处理未关闭、2-已关闭';

ALTER TABLE `ticket_reply`
    CHANGE `id` `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `ticket_id` `ticket_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '工单ID',
    CHANGE `user_id` `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复用户的ID';

RENAME TABLE `user_balance_log` TO `user_credit_log`;
ALTER TABLE `user_credit_log`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '账号ID',
    CHANGE `order_id` `order_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
    CHANGE `before` `before`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生前余额，单位分',
    CHANGE `after` `after`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生后金额，单位分',
    CHANGE `amount` `amount`     INT(10)          NOT NULL DEFAULT '0' COMMENT '发生金额，单位分',
    CHANGE `desc` `description`  VARCHAR(255) DEFAULT '' COMMENT '操作描述';

ALTER TABLE `user_traffic_modify_log`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `order_id` `order_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发生的订单ID',
    CHANGE `desc` `description`  VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '描述';

ALTER TABLE `referral_apply`
    CHANGE `id` `id`               INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `before` `before`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作前可提现金额，单位分',
    CHANGE `after` `after`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作后可提现金额，单位分',
    CHANGE `amount` `amount`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本次提现金额，单位分',
    CHANGE `link_logs` `link_logs` TEXT             NOT NULL COMMENT '关联返利日志ID，例如：1,3,4',
    CHANGE `status` `status`       TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款';

ALTER TABLE `referral_log`
    CHANGE `id` `id`                   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`         INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `ref_user_id` `ref_user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推广人ID',
    CHANGE `order_id` `order_id`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关联订单ID',
    CHANGE `amount` `amount`           INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '消费金额，单位分',
    CHANGE `ref_amount` `ref_amount`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '返利金额',
    CHANGE `status` `status`           TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：0-未提现、1-审核中、2-已提现';

ALTER TABLE `notification_log`
    CHANGE `id` `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type`     TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-ServerChan、3-Bark、4-Telegram',
    CHANGE `status` `status` TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '状态：-1发送失败、0-等待发送、1-发送成功';

ALTER TABLE `sensitive_words`
    CHANGE `id` `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type` TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-黑名单、2-白名单';

ALTER TABLE `user_subscribe`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `code` `code`         CHAR(8) DEFAULT '' COMMENT '订阅地址唯一识别码',
    CHANGE `times` `times`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '地址请求次数',
    CHANGE `status` `status`     BIT              NOT NULL DEFAULT 1 COMMENT '状态：0-禁用、1-启用',
    CHANGE `ban_time` `ban_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封禁时间';

ALTER TABLE `user_subscribe_log`
    CHANGE `id` `id`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `sid` `sid` INT(10) UNSIGNED DEFAULT NULL COMMENT '对应user_subscribe的id';

ALTER TABLE `user_traffic_daily`
    CHANGE `id` `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `node_id` `node_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    CHANGE `u` `u`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    CHANGE `d` `d`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    CHANGE `total` `total`     BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量';

ALTER TABLE `user_traffic_hourly`
    CHANGE `id` `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `node_id` `node_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID，0表示统计全部节点',
    CHANGE `u` `u`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    CHANGE `d` `d`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    CHANGE `total` `total`     BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量';

ALTER TABLE `ss_node_traffic_daily`
    CHANGE `id` `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `u` `u`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    CHANGE `d` `d`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    CHANGE `total` `total`     BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量';

ALTER TABLE `ss_node_traffic_hourly`
    CHANGE `id` `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `u` `u`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传流量',
    CHANGE `d` `d`             BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载流量',
    CHANGE `total` `total`     BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总流量';

ALTER TABLE `user_ban_log`
    CHANGE `id` `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `minutes` `minutes`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封禁账号时长，单位分钟',
    CHANGE `desc` `description` VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '操作描述',
    CHANGE `status` `status`    BIT              NOT NULL DEFAULT 0 COMMENT '状态：0-未处理、1-已处理';

DROP TABLE user_label;
DROP TABLE goods_label;

ALTER TABLE `country`
    CHANGE `id` `id`     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `code` `code` VARCHAR(5)       NOT NULL DEFAULT '' COMMENT '代码';

ALTER TABLE `payment`
    CHANGE `id` `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `sn` `trade_no`     VARCHAR(64)      DEFAULT NULL COMMENT '支付单号（本地订单号）',
    CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    CHANGE `oid` `oid`         INT(10) UNSIGNED DEFAULT NULL COMMENT '本地订单ID',
    CHANGE `amount` `amount`   INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '金额，单位分',
    CHANGE `status` `status`   TINYINT(1)       NOT NULL DEFAULT '0' COMMENT '支付状态：-1-支付失败、0-等待支付、1-支付成功';

DROP TABLE `payment_callback`;
CREATE TABLE `payment_callback`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `trade_no`     VARCHAR(64)      DEFAULT NULL COMMENT '本地订单号',
    `out_trade_no` VARCHAR(64)      DEFAULT NULL COMMENT '外部订单号（支付平台）',
    `amount`       INT(10) UNSIGNED DEFAULT NULL COMMENT '交易金额，单位分',
    `status`       BIT              DEFAULT NULL COMMENT '交易状态：0-失败、1-成功',
    `created_at`   DATETIME         DEFAULT NULL,
    `updated_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='支付回调日志';

ALTER TABLE `marketing`
    CHANGE `id` `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `type` `type`     TINYINT(1)       NOT NULL COMMENT '类型：1-邮件群发',
    CHANGE `status` `status` TINYINT(1)       NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功';

ALTER TABLE `user_login_log`
    CHANGE `id` `id`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `user_id` `user_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE `ip` `ip`             VARCHAR(128)     NOT NULL,
    CHANGE `country` `country`   VARCHAR(128)     NOT NULL,
    CHANGE `province` `province` VARCHAR(128)     NOT NULL,
    CHANGE `city` `city`         VARCHAR(128)     NOT NULL,
    CHANGE `county` `county`     VARCHAR(128)     NOT NULL,
    CHANGE `isp` `isp`           VARCHAR(128)     NOT NULL,
    CHANGE `area` `area`         VARCHAR(255)     NOT NULL;

ALTER TABLE `ss_node_ip`
    CHANGE `id` `id`                 INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
    CHANGE `node_id` `node_id`       INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '节点ID',
    CHANGE `user_id` `user_id`       INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `port` `port`             SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '端口',
    CHANGE `type` `type`             CHAR(3)              NOT NULL DEFAULT 'tcp' COMMENT '类型：all、tcp、udp',
    CHANGE `created_at` `created_at` INT(10) UNSIGNED     NOT NULL DEFAULT '0' COMMENT '上报时间';

DROP TABLE IF EXISTS `rule`;
CREATE TABLE `rule`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       TINYINT(1)       NOT NULL DEFAULT '1' COMMENT '类型：1-正则表达式、2-域名、3-IP、4-协议',
    `name`       VARCHAR(100)     NOT NULL COMMENT '规则描述',
    `pattern`    TEXT             NOT NULL COMMENT '规则值',
    `created_at` DATETIME         NOT NULL,
    `updated_at` DATETIME         NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则';
INSERT INTO `rule`(`id`, `type`, `name`, `pattern`, `created_at`, `updated_at`)
VALUES (1, '1', '360',
        '(.*\.||)(^360|0360|1360|3600|360safe|^so|qhimg|qhmsg|^yunpan|qihoo|qhcdn|qhupdate|360totalsecurity|360shouji|qihucdn|360kan|secmp)\.(cn|com|net)',
        '2019-07-19 15:04:11', '2019-07-19 15:04:11'),
       (2, '1', '腾讯管家', '(\.guanjia\.qq\.com|qqpcmgr|QQPCMGR)', '2019-07-19 15:04:11', '2019-07-19 15:04:11'),
       (3, '1', '金山毒霸', '(.*\.||)(rising|kingsoft|duba|xindubawukong|jinshanduba)\.(com|net|org)',
        '2019-07-19 15:04:11', '2019-07-19 15:04:11'),
       (4, '1', '暗网相关', '(.*\.||)(netvigator|torproject)\.(cn|com|net|org)', '2019-07-19 15:04:11',
        '2019-07-19 15:04:11'),
       (5, '1', '百度定位',
        '(api|ps|sv|offnavi|newvector|ulog\\.imap|newloc|tracknavi)(\\.map|)\\.(baidu|n\\.shifen)\\.com',
        '2019-07-19 15:05:06', '2019-07-19 15:05:06'),
       (6, '1', '法轮功类',
        '(.*\\.||)(dafahao|minghui|dongtaiwang|dajiyuan|falundata|shenyun|tuidang|epochweekly|epochtimes|ntdtv|falundafa|wujieliulan|zhengjian)\\.(org|com|net)',
        '2019-07-19 15:05:46', '2019-07-19 15:05:46'),
       (7, '1', 'BT扩展名',
        '(torrent|\\.torrent|peer_id=|info_hash|get_peers|find_node|BitTorrent|announce_peer|announce\\.php\\?passkey=)',
        '2019-07-19 15:06:07', '2019-07-19 15:06:07'),
       (8, '1', '邮件滥发',
        '((^.*\@)(guerrillamail|guerrillamailblock|sharklasers|grr|pokemail|spam4|bccto|chacuo|027168)\.(info|biz|com|de|net|org|me|la)|Subject|HELO|SMTP)',
        '2019-07-19 15:06:20', '2019-07-19 15:06:20'),
       (9, '1', '迅雷下载', '(.?)(xunlei|sandai|Thunder|XLLiveUD)(.)', '2019-07-19 15:06:31', '2019-07-19 15:06:31'),
       (10, '1', '大陆应用',
        '(.*\\.||)(qq|163|sohu|sogoucdn|sogou|uc|58|taobao|qpic|bilibili|hdslb|sina|douban|doubanio|xiaohongshu|sinaimg|weibo|xiaomi)\\.(org|com|net|cn)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (11, '1', '大陆银行',
        '(.*\\.||)(icbc|ccb|boc|bankcomm|abchina|cmbchina|psbc|cebbank|cmbc|pingan|spdb|citicbank|cib|hxb|bankofbeijing|hsbank|tccb|4001961200|bosc|hkbchina|njcb|nbcb|lj-bank|bjrcb|jsbchina|gzcb|cqcbank|czbank|hzbank|srcb|cbhb|cqrcb|grcbank|qdccb|bocd|hrbcb|jlbank|bankofdl|qlbchina|dongguanbank|cscb|hebbank|drcbank|zzbank|bsb|xmccb|hljrcc|jxnxs|gsrcu|fjnx|sxnxs|gx966888|gx966888|zj96596|hnnxs|ahrcu|shanxinj|hainanbank|scrcu|gdrcu|hbxh|ynrcc|lnrcc|nmgnxs|hebnx|jlnls|js96008|hnnx|sdnxs)\\.(org|com|net|cn)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (12, '1', '台湾银行',
        '(.*\\.||)(firstbank|bot|cotabank|megabank|tcb-bank|landbank|hncb|bankchb|tbb|ktb|tcbbank|scsb|bop|sunnybank|kgibank|fubon|ctbcbank|cathaybk|eximbank|bok|ubot|feib|yuantabank|sinopac|esunbank|taishinbank|jihsunbank|entiebank|hwataibank|csc|skbank)\\.(org|com|net|tw)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (13, '1', '大陆第三方支付',
        '(.*\\.||)(alipay|baifubao|yeepay|99bill|95516|51credit|cmpay|tenpay|lakala|jdpay)\\.(org|com|net|cn)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (14, '1', '台湾特供', '(.*\.||)(visa|mycard|mastercard|gov|gash|beanfun|bank|line)\.(org|com|net|cn|tw|jp|kr)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (15, '1', '涉政治类',
        '(.*\\.||)(shenzhoufilm|secretchina|renminbao|aboluowang|mhradio|guangming|zhengwunet|soundofhope|yuanming|zhuichaguoji|fgmtv|xinsheng|shenyunperformingarts|epochweekly|tuidang|shenyun|falundata|bannedbook|pincong)\\.(org|com|net|rocks)',
        '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
       (16, '1', '流媒体',
        '(.*\.||)(youtube|googlevideo|hulu|netflix|nflxvideo|akamai|nflximg|hbo|mtv|bbc|tvb)\.(org|club|com|net|tv)',
        '2019-11-19 15:04:11', '2019-11-19 15:04:11'),
       (17, '1', '测速类', '(.*\.||)(fast|speedtest)\.(org|com|net|cn)', '2019-11-19 15:04:11', '2019-11-19 15:04:11');

DROP TABLE IF EXISTS `rule_group`;
CREATE TABLE `rule_group`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       BIT          DEFAULT 1 COMMENT '模式：1-阻断、0-放行',
    `name`       VARCHAR(255) DEFAULT NULL COMMENT '分组名称',
    `rules`      TEXT COMMENT '关联的规则ID，多个用,号分隔',
    `nodes`      TEXT COMMENT '关联的节点ID，多个用,号分隔',
    `created_at` DATETIME     DEFAULT NULL,
    `updated_at` DATETIME     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则分组';

INSERT INTO `rule_group`(`id`, `type`, `name`, `rules`, `nodes`, `created_at`, `updated_at`)
VALUES (1, 1, '默认', '1,2,3,4,5,6,7,8,9,10,11,12,13,14', NULL, '2019-10-26 15:29:48', '2019-10-26 15:29:48');

DROP TABLE `device`;

CREATE TABLE `rule_group_node`
(
    `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_group_id` INT(10) UNSIGNED DEFAULT '0' COMMENT '规则分组ID',
    `node_id`       INT(10) UNSIGNED DEFAULT '0' COMMENT '节点ID',
    `created_at`    DATETIME         DEFAULT NULL,
    `updated_at`    DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='审计规则分组节点关联表';

CREATE TABLE `rule_log`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    `node_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址',
    `reason`     VARCHAR(255)     NOT NULL DEFAULT '' COMMENT '触发原因',
    `created_at` DATETIME         NOT NULL,
    `updated_at` DATETIME         NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx` (`user_id`, `node_id`, `rule_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='触发审计规则日志表';

CREATE TABLE `node_rule`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `rule_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '审计规则ID',
    `is_black`   BIT              NOT NULL DEFAULT 1 COMMENT '是否黑名单模式：0-不是、1-是',
    `created_at` DATETIME         NOT NULL,
    `updated_at` DATETIME         NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点审计规则关联';

ALTER TABLE `migrations`
    CHANGE `batch` `batch` INT(10) UNSIGNED NOT NULL;
