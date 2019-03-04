-- 修改字段默认值
ALTER TABLE `ss_node`
	CHANGE COLUMN `is_subscribe` `is_subscribe` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '是否允许用户订阅该节点：0-否、1-是' AFTER `monitor_url`,
	CHANGE COLUMN `compatible` `compatible` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '兼容SS' AFTER `is_tcp_check`,
	CHANGE COLUMN `single` `single` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '单端口多用户：0-否、1-是' AFTER `compatible`;
