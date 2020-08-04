-- 规则表
CREATE TABLE `rule` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` CHAR(10) NOT NULL DEFAULT 'domain' COMMENT '类型：domain-域名（单一非通配）、ipv4-IPv4地址、ipv6-IPv6地址、reg-正则表达式',
	`regular` VARCHAR(255) NOT NULL COMMENT '规则：域名、IP、正则表达式',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM COLLATE='utf8_general_ci' COMMENT='规则表';


-- 节点访问规则关联表
CREATE TABLE `ss_node_deny` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`node_id` INT(11) NOT NULL DEFAULT '0',
	`rule_id` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM COLLATE='utf8_general_ci' COMMENT='节点访问规则关联表';

