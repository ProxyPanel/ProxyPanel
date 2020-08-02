-- 运行完sql后， 运行php artisan fixDailyTrafficLogError 完成升级

-- 注意：本次更新涉及时间修改，请不要在23:50之后运行，以防换日期导致记录出问题。

ALTER TABLE `ss_node`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `ss_node_ping`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `user`
    CHANGE `t` `t`                   INT(10) UNSIGNED DEFAULT NULL COMMENT '最后使用时间',
    CHANGE `ban_time` `ban_time`     INT(10) UNSIGNED DEFAULT NULL COMMENT '封禁到期时间',
    CHANGE `reset_time` `reset_time` DATE     NULL COMMENT '流量重置日期',
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `user_group`
    CHANGE `name` `name` VARCHAR(255) NOT NULL COMMENT '分组名称';

ALTER TABLE `article`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间',
    CHANGE `deleted_at` `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间';

ALTER TABLE `invite`
    CHANGE `dateline` `dateline`     DATETIME NOT NULL COMMENT '有效期至',
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `verify`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `verify_code`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `goods`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `coupon`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间',
    CHANGE `deleted_at` `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间';

ALTER TABLE `coupon_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `products_pool`
    CHANGE `name` `name`             VARCHAR(255)     NOT NULL COMMENT '名称',
    CHANGE `min_amount` `min_amount` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '适用最小金额，单位分',
    CHANGE `max_amount` `max_amount` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '适用最大金额，单位分',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间';

ALTER TABLE `order`
    CHANGE `expire_at` `expired_at`  DATETIME DEFAULT NULL COMMENT '过期时间',
    CHANGE `pay_type` `pay_type`     TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal',
    CHANGE `created_at` `created_at` DATETIME            NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME            NOT NULL COMMENT '最后更新时间';

ALTER TABLE `ticket`
    CHANGE `admin_id` `admin_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间';

ALTER TABLE `ticket_reply`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `user_credit_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间';

ALTER TABLE `user_traffic_modify_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `referral_apply`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `referral_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `notification_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `user_subscribe`
    CHANGE `code` `code`             CHAR(8)  NOT NULL DEFAULT '' COMMENT '订阅地址唯一识别码',
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `user_subscribe_log`
    CHANGE `sid` `sid`                   INT(10) UNSIGNED NOT NULL COMMENT '对应user_subscribe的id',
    CHANGE `request_time` `request_time` DATETIME         NOT NULL COMMENT '请求时间';

ALTER TABLE `user_traffic_daily`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `user_traffic_hourly`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `ss_node_traffic_daily`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `ss_node_traffic_hourly`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `user_ban_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `payment`
    CHANGE `trade_no` `trade_no`     VARCHAR(64)      NOT NULL COMMENT '支付单号（本地订单号）',
    CHANGE `oid` `oid`               INT(10) UNSIGNED NOT NULL COMMENT '本地订单ID',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间';

DROP TABLE `payment_callback`;
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

ALTER TABLE `marketing`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';

ALTER TABLE `user_login_log`
    CHANGE `user_id` `user_id`       INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
    CHANGE `ip` `ip`                 VARCHAR(45)      NOT NULL COMMENT 'IP地址',
    CHANGE `country` `country`       VARCHAR(128)     NOT NULL COMMENT '国家',
    CHANGE `province` `province`     VARCHAR(128)     NOT NULL COMMENT '省份',
    CHANGE `city` `city`             VARCHAR(128)     NOT NULL COMMENT '城市',
    CHANGE `county` `county`         VARCHAR(128)     NOT NULL COMMENT '郡县',
    CHANGE `isp` `isp`               VARCHAR(128)     NOT NULL COMMENT '运营商',
    CHANGE `area` `area`             VARCHAR(255)     NOT NULL COMMENT '地区',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `rule_group`
    CHANGE `type` `type`             BIT          NOT NULL DEFAULT 1 COMMENT '模式：1-阻断、0-放行',
    CHANGE `name` `name`             VARCHAR(255) NOT NULL COMMENT '分组名称',
    CHANGE `rules` `rules`           TEXT DEFAULT NULL COMMENT '关联的规则ID，多个用,号分隔',
    CHANGE `nodes` `nodes`           TEXT DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔',
    CHANGE `created_at` `created_at` DATETIME     NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME     NOT NULL COMMENT '最后更新时间';

ALTER TABLE `rule_group_node`
    CHANGE `rule_group_id` `rule_group_id` INT(10) UNSIGNED NOT NULL COMMENT '规则分组ID',
    CHANGE `node_id` `node_id`             INT(10) UNSIGNED NOT NULL COMMENT '节点ID',
    CHANGE `created_at` `created_at`       DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at`       DATETIME         NOT NULL COMMENT '最后更新时间';

ALTER TABLE `rule_log`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    DROP `updated_at`;

ALTER TABLE `node_rule`
    CHANGE `node_id` `node_id`       INT(10) UNSIGNED NULL COMMENT '节点ID',
    CHANGE `rule_id` `rule_id`       INT(10) UNSIGNED NULL COMMENT '审计规则ID',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间';

ALTER TABLE `node_auth`
    CHANGE `node_id` `node_id`       INT(10) UNSIGNED NOT NULL COMMENT '授权节点ID',
    CHANGE `key` `key`               CHAR(16)         NOT NULL COMMENT '认证KEY',
    CHANGE `secret` `secret`         CHAR(8)          NOT NULL COMMENT '通信密钥',
    CHANGE `created_at` `created_at` DATETIME         NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME         NOT NULL COMMENT '最后更新时间';

ALTER TABLE `node_certificate`
    CHANGE `created_at` `created_at` DATETIME NOT NULL COMMENT '创建时间',
    CHANGE `updated_at` `updated_at` DATETIME NOT NULL COMMENT '最后更新时间';