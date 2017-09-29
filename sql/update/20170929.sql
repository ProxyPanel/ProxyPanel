ALTER TABLE `email_log` ADD COLUMN `error` text COMMENT '发送失败抛出的异常信息' AFTER `status`;
INSERT INTO `config` VALUES ('25', 'expire_warning', 0);
INSERT INTO `config` VALUES ('26', 'expire_days', 15);