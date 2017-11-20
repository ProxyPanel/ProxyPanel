ALTER TABLE `ss_node` ADD COLUMN `single` tinyint(4) DEFAULT '0' COMMENT '单端口多用户' AFTER `compatible`;
ALTER TABLE `ss_node` ADD COLUMN `single_force` tinyint(4) DEFAULT NULL COMMENT '模式：0-兼容模式、1-严格模式' AFTER `single`;
ALTER TABLE `ss_node` ADD COLUMN `single_port` varchar(50) DEFAULT '' COMMENT '端口号，用,号分隔' AFTER `single_force`;
ALTER TABLE `ss_node` ADD COLUMN `single_passwd` varchar(50) DEFAULT '' COMMENT '密码' AFTER `single_port`;
ALTER TABLE `ss_node` ADD COLUMN `single_method` varchar(50) DEFAULT '' COMMENT '加密方式' AFTER `single_passwd`;
ALTER TABLE `ss_node` ADD COLUMN `single_protocol` varchar(50) NOT NULL DEFAULT '' COMMENT '协议' AFTER `single_method`;

ALTER TABLE `user` ADD COLUMN `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁到期时间' AFTER `expire_time`;
ALTER TABLE `ss_node` ADD COLUMN `desc` varchar(255) DEFAULT '' COMMENT '节点简单描述' AFTER `server`;


INSERT INTO `config` VALUES ('33', 'is_traffic_ban', 1);
INSERT INTO `config` VALUES ('34', 'traffic_ban_value', 10);
INSERT INTO `config` VALUES ('35', 'traffic_ban_time', 60);

ALTER TABLE `article` ADD COLUMN `type` tinyint(4) DEFAULT '1' COMMENT '类型：1-文章、2-公告' AFTER `content`;


CREATE TABLE `user_ban_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `minutes` int(11) NOT NULL DEFAULT '0' COMMENT '封禁账号市场，单位分钟',
  `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作描述',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0-未处理、1-已处理',
  `created_at` datetime DEFAULT NULL COMMENT ' 创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户封禁日志';


CREATE TABLE `ss_node_traffic_daily` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '节点ID',
  `u` bigint(20) NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(255) DEFAULT '' COMMENT '总流量（带单位）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_node_id` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ss_node_traffic_hourly` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '节点ID',
  `u` bigint(20) NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(255) DEFAULT '' COMMENT '总流量（带单位）',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '最后一起更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_node_id` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


