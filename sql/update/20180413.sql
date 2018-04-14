-- config表字段类型变更，为了加入统计代码和客服代码
ALTER TABLE `config`
CHANGE COLUMN `value` `value` TEXT NULL COMMENT '配置值' AFTER `name`;

-- 加入网站统计代码、在线客服代码
INSERT INTO `config` VALUES ('55', 'website_analytics', '');
INSERT INTO `config` VALUES ('56', 'website_customer_service', '');