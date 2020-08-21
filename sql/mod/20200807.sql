ALTER TABLE `article`
    DROP `author`;

ALTER TABLE `config`
    DROP PRIMARY KEY,
    DROP `id`,
    CHANGE `name` `name` VARCHAR(255) NOT NULL COMMENT '配置名',
    ADD PRIMARY KEY (`name`);

ALTER TABLE `coupon`
    CHANGE `usage_count` `usable_times`   SMALLINT UNSIGNED DEFAULT NULL COMMENT '可使用次数',
    ADD `value`                           INT(10) UNSIGNED NOT NULL COMMENT '折扣金额(元)/折扣力度' AFTER `usable_times`,
    CHANGE `rule` `rule`                  INT(10) UNSIGNED  DEFAULT NULL COMMENT '使用限制(元)',
    CHANGE `available_start` `start_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '有效期开始',
    CHANGE `available_end` `end_time`     INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '有效期结束';

-- 注意！！ 本sql需要分 2 段分开运行
-- 运行 php artisan updateCoupon

ALTER TABLE `coupon`
    DROP `amount`,
    DROP `discount`;

DROP TABLE `country`;
CREATE TABLE `country`
(
    `code` CHAR(2)     NOT NULL COMMENT 'ISO国家代码',
    `name` VARCHAR(10) NOT NULL COMMENT '名称',
    PRIMARY KEY (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='国家代码';
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

ALTER TABLE `user`
    DROP `enable_time`,
    CHANGE `expire_time` `expired_at`  DATE NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
    CHANGE `referral_uid` `inviter_id` INT(10) UNSIGNED DEFAULT NULL COMMENT '邀请人';

ALTER TABLE `invite`
    CHANGE `uid` `inviter_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '邀请ID',
    CHANGE `fuid` `invitee_id` INT(10) UNSIGNED DEFAULT NULL COMMENT '受邀ID';

ALTER TABLE `referral_log`
    CHANGE `user_id` `invitee_id`     INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
    CHANGE `ref_user_id` `inviter_id` INT(10) UNSIGNED NOT NULL COMMENT '推广人ID',
    CHANGE `ref_amount` `commission`  INT(10) UNSIGNED NOT NULL COMMENT '返利金额';

ALTER TABLE `order`
    DROP PRIMARY KEY,
    CHANGE `oid` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD PRIMARY KEY (`id`);

ALTER TABLE `user_subscribe_log`
    CHANGE `sid` `user_subscribe_id` INT(10) UNSIGNED NOT NULL COMMENT '对应user_subscribe的id',
    DROP INDEX `sid`,
    ADD INDEX `user_subscribe_id` (`user_subscribe_id`);

ALTER TABLE `user_baned_log`
    CHANGE `minutes` `time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封禁账号时长，单位分钟';

ALTER TABLE `payment`
    CHANGE `oid` `order_id` INT(10) UNSIGNED NOT NULL COMMENT '本地订单ID';