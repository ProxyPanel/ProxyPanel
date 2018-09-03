-- 更改用户登录IP日志表结构
ALTER TABLE `user_login_log`
	ALTER `region` DROP DEFAULT,
	ALTER `isp_id` DROP DEFAULT;

ALTER TABLE `user_login_log`
	CHANGE COLUMN `region` `province` CHAR(20) NOT NULL AFTER `country`,
	CHANGE COLUMN `isp_id` `area` CHAR(20) NOT NULL AFTER `isp`,
	DROP COLUMN `country_id`,
	DROP COLUMN `region_id`,
	DROP COLUMN `city_id`,
	DROP COLUMN `county_id`;

ALTER TABLE `user_login_log`
	ALTER `country` DROP DEFAULT,
	ALTER `province` DROP DEFAULT,
	ALTER `city` DROP DEFAULT,
	ALTER `county` DROP DEFAULT,
	ALTER `isp` DROP DEFAULT,
	ALTER `area` DROP DEFAULT;
ALTER TABLE `user_login_log`
	CHANGE COLUMN `country` `country` VARCHAR(80) NOT NULL AFTER `ip`,
	CHANGE COLUMN `province` `province` VARCHAR(80) NOT NULL AFTER `country`,
	CHANGE COLUMN `city` `city` VARCHAR(80) NOT NULL AFTER `province`,
	CHANGE COLUMN `county` `county` VARCHAR(80) NOT NULL AFTER `city`,
	CHANGE COLUMN `isp` `isp` VARCHAR(50) NOT NULL AFTER `county`,
	CHANGE COLUMN `area` `area` VARCHAR(200) NOT NULL AFTER `isp`;