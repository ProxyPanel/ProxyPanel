-- 加入服务类型
ALTER TABLE `ss_node`
	ADD COLUMN `type` TINYINT NOT NULL DEFAULT '1' COMMENT '服务类型：1-SS、2-V2ray' AFTER `id`;

-- 加入V2ray额外ID
ALTER TABLE `ss_node`
	ADD COLUMN `v2_alter_id` INT(11) NOT NULL DEFAULT '16' COMMENT 'V2ray额外ID' AFTER `status`;

-- 加入V2ray端口
ALTER TABLE `ss_node`
	ADD COLUMN `v2_port` INT(11) NOT NULL DEFAULT '0' COMMENT 'V2ray端口' AFTER `v2_alter_id`;

-- 加入V2ray传输协议
ALTER TABLE `ss_node`
	ADD COLUMN `v2_net` VARCHAR(16) NOT NULL DEFAULT 'tcp' COMMENT 'V2ray传输协议' AFTER `v2_port`;

-- 加入V2ray伪装类型
ALTER TABLE `ss_node`
	ADD COLUMN `v2_type` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT 'V2ray伪装类型' AFTER `v2_net`;

-- 加入V2ray伪装的域名
ALTER TABLE `ss_node`
	ADD COLUMN `v2_host` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'V2ray伪装的域名' AFTER `v2_type`;

-- 加入V2rayWS/H2路径
ALTER TABLE `ss_node`
	ADD COLUMN `v2_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'V2ray WS/H2路径' AFTER `v2_host`;

-- 加入V2ray底层传输安全
ALTER TABLE `ss_node`
	ADD COLUMN `v2_tls` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'V2ray底层传输安全 0 未开启 1 开启' AFTER `v2_path`;

-- 加入VmessId
ALTER TABLE `user`
	ADD COLUMN `vmess_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'V2ray用户ID' AFTER `passwd`;
