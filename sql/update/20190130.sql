-- 工单加入最后更新时间
ALTER TABLE `ticket`
	ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL COMMENT '最后更新时间' AFTER `created_at`;


-- 工单回复加入最后更新时间
ALTER TABLE `ticket_reply`
	ADD COLUMN `updated_at` DATETIME NULL COMMENT '最后更新时间' AFTER `created_at`;
