-- 适配V2Ray上报IP、VNET单端口上报IP
ALTER TABLE `ss_node_ip`
	ADD COLUMN `user_id` INT NOT NULL DEFAULT '0' COMMENT '用户ID' AFTER `node_id`;
