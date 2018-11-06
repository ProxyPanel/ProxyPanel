-- 增加‘验证注册验证码’开关
INSERT INTO `config` values ('71', 'is_verify_register', 0);

-- user_id 字段改为 type 字段
ALTER TABLE `email_log`
	CHANGE COLUMN `user_id` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-serverChan' AFTER `id`;

-- 增加address字段
ALTER TABLE `email_log`
  ADD COLUMN `address` VARCHAR(255) NOT NULL COMMENT '收信地址' AFTER `type`;

-- 增加注册验证码表
CREATE TABLE `verify_code` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(128) NOT NULL COMMENT '用户邮箱' COLLATE 'utf8mb4_unicode_ci',
	`code` CHAR(6) NOT NULL COMMENT '验证码' COLLATE 'utf8mb4_unicode_ci',
	`status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
	`created_at` DATETIME NULL DEFAULT NULL COMMENT '创建时间',
	`updated_at` DATETIME NULL DEFAULT NULL COMMENT '最后更新时间',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='注册激活验证码';