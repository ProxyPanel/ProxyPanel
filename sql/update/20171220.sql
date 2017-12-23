INSERT INTO `config` VALUES ('41', 'is_subscribe_ban', 1);
INSERT INTO `config` VALUES ('42', 'subscribe_ban_times', 20);
INSERT INTO `config` VALUES ('43', 'paypal_status', 0);
INSERT INTO `config` VALUES ('44', 'paypal_client_id', '');
INSERT INTO `config` VALUES ('45', 'paypal_client_secret', '');


ALTER TABLE `user_subscribe` ADD COLUMN `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时间' AFTER `status`;
ALTER TABLE `user_subscribe` ADD COLUMN `ban_desc` varchar(50) NOT NULL DEFAULT '' COMMENT '封禁理由' AFTER `ban_time`;

ALTER TABLE `goods` ADD COLUMN `sku` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '商品服务SKU' AFTER `id`;

CREATE TABLE `paypal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `invoice_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '账单号',
  `items` text COLLATE utf8mb4_unicode_ci COMMENT '商品信息，json格式',
  `response_data` text COLLATE utf8mb4_unicode_ci COMMENT '收货地址，json格式',
  `error` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误信息',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 加入国家
INSERT INTO `country` VALUES ('52', '捷克', 'cz');
INSERT INTO `country` VALUES ('53', '摩尔多瓦', 'md');
INSERT INTO `country` VALUES ('54', '西班牙', 'es');
INSERT INTO `country` VALUES ('55', '巴基斯坦', 'pk');
INSERT INTO `country` VALUES ('56', '葡萄牙', 'pt');
INSERT INTO `country` VALUES ('57', '匈牙利', 'hu');
INSERT INTO `country` VALUES ('58', '阿根廷', 'ar');