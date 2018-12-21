-- 优化定时任务执行时间（统计流量）
ALTER TABLE `user_traffic_log`
	DROP INDEX `idx_user`,
	DROP INDEX `idx_node`;

ALTER TABLE `user_traffic_log`
	DROP INDEX `idx_user_node`,
	ADD INDEX `idx_user_node` (`user_id`, `log_time`, `node_id`) USING BTREE,
	ADD INDEX `idx_node_time` (`node_id`, `log_time`);
