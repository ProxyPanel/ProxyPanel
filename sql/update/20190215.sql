-- 邮件投递记录增加最后更新字段
ALTER TABLE `email_log`
	ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL COMMENT '最后更新时间' AFTER `created_at`;
