INSERT INTO `config` VALUES ('41', 'is_subscribe_ban', 1);
INSERT INTO `config` VALUES ('42', 'subscribe_ban_times', 20);
INSERT INTO `config` VALUES ('43', 'paypal_status', 0);
INSERT INTO `config` VALUES ('44', 'paypal_client_id', '');
INSERT INTO `config` VALUES ('45', 'paypal_client_secret', '');


ALTER TABLE `user_subscribe` ADD COLUMN `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时间' AFTER `status`;
ALTER TABLE `user_subscribe` ADD COLUMN `ban_desc` varchar(50) NOT NULL DEFAULT '' COMMENT '封禁理由' AFTER `ban_time`;