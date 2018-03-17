-- 加入有赞云支付
INSERT INTO `config` VALUES ('50', 'is_youzan', 0);
INSERT INTO `config` VALUES ('51', 'youzan_client_id', '');
INSERT INTO `config` VALUES ('52', 'youzan_client_secret', '');
INSERT INTO `config` VALUES ('53', 'kdt_id', '');

-- 更新payment表
DROP TABLE `payment`;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `oid` int(11) DEFAULT NULL COMMENT '本地订单ID',
  `orderId` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '本地订单长ID',
  `pay_way` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '支付类型：1-扫码支付',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '金额，单位分',
  `qr_id` int(11) NOT NULL DEFAULT '0' COMMENT '有赞生成的支付单ID',
  `qr_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '有赞生成的支付二维码URL',
  `qr_code` text COLLATE utf8mb4_unicode_ci COMMENT '有赞生成的支付二维码图片base64',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态：-1-支付失败、0-等待支付、1-支付成功',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;