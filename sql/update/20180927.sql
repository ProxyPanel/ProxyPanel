-- ----------------------------
-- Table structure for `user_traffic_modify_log`
-- ----------------------------
CREATE TABLE `user_traffic_modify_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
	`order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '发生的订单ID',
	`before` INT(11) NOT NULL DEFAULT '0',
	`after` INT(11) NOT NULL DEFAULT '0',
	`desc` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '描述',
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户流量变动日志';