-- 节点订阅地址
INSERT INTO `config` VALUES ('48', 'subscribe_domain', '');

-- 节点订阅地址缩短并改为可以识别大小写
ALTER TABLE `user_subscribe`
MODIFY COLUMN `code`  char(20) BINARY CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT '' COMMENT '订阅地址唯一识别码' AFTER `user_id`;

-- 标签
CREATE TABLE `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签';

INSERT INTO `label` VALUES ('1', '电信', '0');
INSERT INTO `label` VALUES ('2', '联通', '0');
INSERT INTO `label` VALUES ('3', '移动', '0');
INSERT INTO `label` VALUES ('4', '教育网', '0');
INSERT INTO `label` VALUES ('5', '其他网络', '0');
INSERT INTO `label` VALUES ('6', '免费体验', '0');

-- 用户标签
CREATE TABLE `user_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `label_id` int(11) NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`id`),
  KEY `idx` (`user_id`,`label_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_label_id` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户标签';

-- 节点标签
CREATE TABLE `ss_node_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `label_id` int(11) NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`id`),
  KEY `idx` (`node_id`,`label_id`),
  KEY `idx_node_id` (`node_id`),
  KEY `idx_label_id` (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='节点标签';