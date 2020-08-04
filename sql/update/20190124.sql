-- 节点是否允许UDP
ALTER TABLE `ss_node`
	ADD COLUMN `is_udp` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '是否允许UDP：0-否、1-是' AFTER `is_nat`;
