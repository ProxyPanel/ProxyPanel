-- 日志表加索引，提升查询速度
ALTER TABLE `user_traffic_log`
ADD INDEX `idx_user_node` (`user_id`, `node_id`) USING BTREE ;

ALTER TABLE `user_traffic_daily`
ADD INDEX `idx_user` (`user_id`) USING BTREE ,
ADD INDEX `idx_user_node` (`user_id`, `node_id`) USING BTREE ;

ALTER TABLE `user_traffic_hourly`
ADD INDEX `idx_user` (`user_id`) USING BTREE ,
ADD INDEX `idx_user_node` (`user_id`, `node_id`) USING BTREE ;

ALTER TABLE `ss_node_info`
ADD INDEX `idx_node_id` (`node_id`) USING BTREE ;

ALTER TABLE `ss_node_online_log`
ADD INDEX `idx_node_id` (`node_id`) USING BTREE ;

-- 去除节点负载信息表无用字段
ALTER TABLE `ss_node_info`
DROP COLUMN `created_at`,
DROP COLUMN `updated_at`;

-- 去除在线用户表无用字段
ALTER TABLE `ss_node_online_log`
DROP COLUMN `created_at`,
DROP COLUMN `updated_at`;



