-- 加入订阅设备表
CREATE TABLE `device` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：0-兼容、1-Shadowsocks(R)、2-V2Ray',
	`platform` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '所属平台：0-其他、1-iOS、2-Android、3-Mac、4-Windows、5-Linux',
	`name` VARCHAR(50) NOT NULL COMMENT '设备名称',
	`status` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '状态：0-禁止订阅、1-允许订阅',
	PRIMARY KEY (`id`)
) COMMENT='设备型号表' ENGINE=MyISAM;

INSERT INTO `device` VALUES ('1', '1', '1', 'Quantumult', 1);
INSERT INTO `device` VALUES ('2', '1', '1', 'Shadowrocket', 1);
INSERT INTO `device` VALUES ('3', '1', '3', 'ShadowsocksX-NG-R', 1);