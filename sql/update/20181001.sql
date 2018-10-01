-- ----------------------------
-- Table structure for `user_traffic_modify_log`
-- ----------------------------
DROP TABLE IF EXISTS `user_traffic_modify_log`;
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

-- 商品表加入商品颜色字段
ALTER TABLE `goods`
	ADD COLUMN `color` VARCHAR(50) NOT NULL DEFAULT 'green' COMMENT '商品颜色' AFTER `days`;

-- 商品表加入是否热销字段
ALTER TABLE `goods`
	ADD COLUMN `is_hot` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否热销：0-否、1-是' AFTER `sort`;

-- 订单表加入邮箱字段
ALTER TABLE `order`
ADD COLUMN `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱' AFTER `coupon_id`;