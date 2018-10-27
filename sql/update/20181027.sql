-- 节点各个端口的连接IP记录表（由节点每60秒上报一次）
CREATE TABLE `ss_node_ip` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '节点ID',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口',
  `type` char(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tcp' COMMENT '类型：tcp、udp',
  `ip` text COLLATE utf8mb4_unicode_ci COMMENT '连接IP：每个IP用,号隔开',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '上报时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;