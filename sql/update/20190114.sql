-- 节点表加入V2ray加密方式
ALTER TABLE `ss_node`
	ADD COLUMN `v2_method` VARCHAR(32) NOT NULL DEFAULT 'aes-128-gcm' COMMENT 'V2ray加密方式' AFTER `v2_port`;
