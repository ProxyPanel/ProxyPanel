INSERT INTO `config` VALUES ('41', 'is_subscribe_ban', 1);
INSERT INTO `config` VALUES ('42', 'subscribe_ban_times', 20);


ALTER TABLE `user_subscribe` ADD COLUMN `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时间' AFTER `status`;
ALTER TABLE `user_subscribe` ADD COLUMN `ban_desc` varchar(50) NOT NULL DEFAULT '' COMMENT '封禁理由' AFTER `ban_time`;