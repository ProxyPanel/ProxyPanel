-- 用户流量变动记录
ALTER TABLE `user_traffic_modify_log`
	CHANGE COLUMN `before` `before` BIGINT NOT NULL DEFAULT '0' COMMENT '操作前流量' AFTER `order_id`,
	CHANGE COLUMN `after` `after` BIGINT NOT NULL DEFAULT '0' COMMENT '操作后流量' AFTER `before`;
