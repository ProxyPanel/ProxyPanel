-- 增加‘验证注册验证码’开关
INSERT INTO `config` values ('71', 'is_verify_register', 0);

-- user_id 字段改为 type 字段
ALTER TABLE `email_log`
	CHANGE COLUMN `user_id` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-serverChan' AFTER `id`;

-- 增加address字段
ALTER TABLE `email_log`
  ADD COLUMN `address` VARCHAR(255) NOT NULL COMMENT '收信地址' AFTER `type`;