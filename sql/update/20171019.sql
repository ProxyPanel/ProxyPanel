ALTER TABLE `user_subscribe_log`
ADD COLUMN `request_header` text COMMENT '请求头部信息' AFTER `request_time`;

ALTER TABLE `user_subscribe`
ADD COLUMN `updated_at` datetime DEFAULT NULL COMMENT '最后更新时间' AFTER `created_at`;