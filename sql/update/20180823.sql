-- 加入登录日志表
CREATE TABLE `user_login_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`ip` CHAR(20) NOT NULL,
	`country` CHAR(20) NOT NULL,
	`country_id` CHAR(20) NOT NULL,
	`region` CHAR(20) NOT NULL,
	`region_id` CHAR(20) NOT NULL,
	`city` CHAR(20) NOT NULL,
	`city_id` CHAR(20) NOT NULL,
	`county` CHAR(20) NOT NULL,
	`county_id` CHAR(20) NOT NULL,
	`isp` CHAR(20) NOT NULL,
	`isp_id` CHAR(20) NOT NULL,
	`created_at` DATETIME NOT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户登录日志';
