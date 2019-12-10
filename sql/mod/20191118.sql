-- 国家表字段改名
ALTER TABLE `country`
    CHANGE COLUMN `country_name` `name` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '名称' COLLATE 'utf8mb4_unicode_ci' AFTER `id`,
    CHANGE COLUMN `country_code` `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '代码' COLLATE 'utf8mb4_unicode_ci' AFTER `name`;

-- 管理员收信地址字段更名
UPDATE `config`
SET name='webmaster_email'
WHERE id = 38;
-- 1是黑名单 0是白名单
UPDATE `config`
SET `name`  = 'sensitiveType',
    `value` = '1'
WHERE id = 58;

-- 节点信息简化
ALTER TABLE `ss_node`
    DROP `single_force`,
    DROP `single_method`,
    DROP `single_protocol`,
    DROP `single_obfs`,
    CHANGE `single` `single`         TINYINT(4)   NOT NULL DEFAULT '0' COMMENT '启用单端口功能：0-否、1-是',
    CHANGE `single_port` `port`      VARCHAR(50)  NULL COMMENT '端口号，用,号分隔',
    CHANGE `single_passwd` `passwd`  VARCHAR(50)  NULL COMMENT '单端口的连接密码',
    CHANGE `protocol` `protocol`     VARCHAR(64)  NOT NULL DEFAULT 'origin' COMMENT '协议',
    CHANGE `obfs` `obfs`             VARCHAR(64)  NOT NULL DEFAULT 'plain' COMMENT '混淆',
    CHANGE `obfs_param` `obfs_param` VARCHAR(255) NOT NULL DEFAULT 'plain' COMMENT '混淆参数';

ALTER TABLE `ss_node_info`
    CHANGE `uptime` `uptime` int(10)      NOT NULL COMMENT '后端存活时长，单位秒',
    CHANGE `load` `load`     VARCHAR(255) NOT NULL COMMENT '负载';

-- 用户表新增最后连接IP，邀请码数字段
ALTER TABLE `user`
    DROP `gender`,
    DROP INDEX `idx_search`,
    ADD INDEX `idx_search` (`enable`, `status`, `port`) USING BTREE,
    MODIFY COLUMN `reg_ip` char(128) NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP' AFTER `is_admin`,
    ADD COLUMN `ip`         char(128) NULL COMMENT '最后连接IP' AFTER `t`,
    ADD COLUMN `invite_num` INT       NOT NULL DEFAULT '0' COMMENT '可生成邀请码数' AFTER `traffic_reset_day`;

-- 敏感词黑白名单
ALTER TABLE `sensitive_words`
    ADD COLUMN `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：1-黑名单、2-白名单' AFTER `id`;

-- ipv6兼容
ALTER TABLE `user_subscribe_log`
    MODIFY COLUMN `request_ip` char(128) NULL DEFAULT NULL COMMENT '请求IP' AFTER `sid`;
ALTER TABLE `user_login_log`
    MODIFY COLUMN `ip` char(128) NOT NULL AFTER `user_id`;

-- 重建用户连接IP日志表索引
ALTER TABLE `ss_node_ip`
    DROP INDEX `idx_node`,
    ADD INDEX `idx_node` (`node_id`),
    ADD INDEX `idx_user` (`user_id`);

-- 重建连接IP信息索引
ALTER TABLE `ss_node_ip`
    DROP INDEX `idx_node`,
    ADD INDEX `idx_node` (`node_id`, `user_id`) USING BTREE;

-- 商品信息修改
ALTER TABLE `goods`
    DROP `is_limit`,
    ADD COLUMN `invite_num` int(11) NOT NULL DEFAULT '0' COMMENT '赠送邀请码数' AFTER `days`,
    ADD COLUMN `limit_num`  int(11) NOT NULL DEFAULT '0' COMMENT '限购数量，默认为0不限购' AFTER `invite_num`;