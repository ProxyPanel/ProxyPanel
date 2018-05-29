-- 用户小时流量记录表加索引，用于加速查询统计
ALTER TABLE `user_traffic_hourly`
ADD INDEX `idx_node` (`node_id`) USING BTREE ,
ADD INDEX `idx_total` (`total`) USING BTREE ,
ADD INDEX `idx_node_total` (`node_id`, `total`) USING BTREE ;


-- 用户小时流量记录表加索引，用于加速查询统计
ALTER TABLE `user_traffic_daily`
ADD INDEX `idx_node` (`node_id`) USING BTREE ,
ADD INDEX `idx_total` (`total`) USING BTREE ,
ADD INDEX `idx_node_total` (`node_id`, `total`) USING BTREE ;