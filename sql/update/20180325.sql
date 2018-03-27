-- 有赞云回调日志
CREATE TABLE `payment_callback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` varchar(50) DEFAULT NULL,
  `yz_id` varchar(50) DEFAULT NULL,
  `kdt_id` varchar(50) DEFAULT NULL,
  `kdt_name` varchar(50) DEFAULT NULL,
  `mode` tinyint(4) DEFAULT NULL,
  `msg` text,
  `sendCount` int(11) DEFAULT NULL,
  `sign` varchar(32) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `test` tinyint(4) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='有赞云回调日志';