ALTER TABLE `user_subscribe`
ADD COLUMN `updated_at` datetime DEFAULT NULL COMMENT '最后更新时间' AFTER `created_at`;

ALTER TABLE `user_subscribe`
ADD COLUMN `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0-禁用、1-启用' AFTER `times`;

INSERT INTO `config` VALUES ('29', 'subscribe_max', 3);