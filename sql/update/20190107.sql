-- 移除节点无用字段、加入是否nat机字段
ALTER TABLE `ss_node`
	ADD COLUMN `is_nat` TINYINT NOT NULL DEFAULT '0' COMMENT '是否为NAT机：0-否、1-是' AFTER `is_subscribe`,
	DROP COLUMN `icmp`,
	DROP COLUMN `tcp`,
	DROP COLUMN `udp`;

ALTER TABLE `ss_node`
	CHANGE COLUMN `traffic` `traffic` INT(11) NOT NULL DEFAULT '1000' COMMENT '每月可用流量，单位G' AFTER `bandwidth`;
