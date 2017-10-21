ALTER TABLE `user_subscribe_log`
ADD COLUMN `request_header` text COMMENT '请求头部信息' AFTER `request_time`;
