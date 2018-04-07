-- 节点表 增加 ipv6 字段
ALTER TABLE `ss_node`
	ADD COLUMN `ipv6` VARCHAR(128) NULL DEFAULT '' COMMENT '服务器IPV6地址' AFTER `ip`;

-- 节点表 修改字段长度
ALTER TABLE `ss_node`
	CHANGE COLUMN `server` `server` VARCHAR(128) NULL DEFAULT NULL COMMENT '服务器域名地址' AFTER `country_code`,
	CHANGE COLUMN `ip` `ip` CHAR(15) NULL DEFAULT NULL COMMENT '服务器IPV4地址' AFTER `server`;
