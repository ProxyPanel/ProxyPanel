-- 设备表加入 请求时头部的识别特征码 字段
ALTER TABLE `device`
	CHANGE COLUMN `platform` `platform` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '所属平台：0-其他、1-iOS、2-Android、3-Mac、4-Windows、5-Linux' AFTER `type`,
	ADD COLUMN `header` VARCHAR(100) NOT NULL COMMENT '请求时头部的识别特征码' AFTER `status`;

-- 重新初始化设备表数据
TRUNCATE TABLE `device`;
INSERT INTO `device` (`id`, `type`, `platform`, `name`, `status`, `header`) VALUES
	(1, 1, 1, 'Quantumult', 1, 'Quantumult'),
	(2, 1, 1, 'Shadowrocket', 1, 'Shadowrocket'),
	(3, 1, 3, 'ShadowsocksX-NG-R', 1, 'ShadowsocksX-NG-R'),
	(4, 1, 1, 'Pepi', 1, 'Pepi'),
	(5, 1, 1, 'Potatso 2', 1, 'Potatso'),
	(6, 1, 1, 'Potatso Lite', 1, 'Potatso'),
	(7, 1, 4, 'ShadowsocksR', 1, 'ShadowsocksR'),
	(8, 2, 4, 'V2RayW', 1, 'V2RayW'),
	(9, 2, 4, 'V2RayN', 1, 'V2RayN'),
	(10, 2, 4, 'V2RayS', 1, 'V2RayS'),
	(11, 2, 4, 'Clash for Windows', 1, 'Clash'),
	(12, 2, 3, 'V2RayX', 1, 'V2RayX'),
	(13, 2, 3, 'V2RayU', 1, 'V2RayU'),
	(14, 2, 3, 'V2RayC', 1, 'V2RayC'),
	(15, 2, 3, 'ClashX', 1, 'ClashX'),
	(16, 2, 1, 'Kitsunebi', 1, 'Kitsunebi'),
	(17, 2, 1, 'Kitsunebi Lite', 1, 'Kitsunebi'),
	(18, 2, 1, 'i2Ray', 1, 'i2Ray'),
	(19, 2, 2, 'BifrostV', 1, 'BifrostV'),
	(20, 2, 2, 'V2RayNG', 1, 'V2RayNG'),
	(21, 2, 2, 'ShadowsocksR', 1, 'okhttp'),
	(22, 2, 2, 'SSRR', 1, 'okhttp');

