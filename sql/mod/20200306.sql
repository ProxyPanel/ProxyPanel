CREATE TABLE `ss_node_ping` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `node_id` INT(11) NOT NULL DEFAULT '0' COMMENT '对应节点id',
  `ct` INT(11) NOT NULL DEFAULT '0' COMMENT '电信',
  `cu` INT(11) NOT NULL DEFAULT '0' COMMENT '联通',
  `cm` INT(11) NOT NULL DEFAULT '0' COMMENT '移动',
  `hk` INT(11) NOT NULL DEFAULT '0' COMMENT '香港',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_node_id` (`node_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='节点ping信息表';