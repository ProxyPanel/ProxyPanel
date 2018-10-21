-- 加入激活类型
ALTER TABLE `verify`
	ADD COLUMN `type` TINYINT NOT NULL DEFAULT '1' COMMENT '激活类型：1-自行激活、2-管理员激活' AFTER `id`;

-- 移除无用username字段
ALTER TABLE `verify`
	DROP COLUMN `username`;