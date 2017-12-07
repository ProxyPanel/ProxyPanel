INSERT INTO `config` VALUES ('37', 'dmf_wepay', '0');
INSERT INTO `config` VALUES ('38', 'dmf_alipay', '0');
INSERT INTO `config` VALUES ('39', 'dmf_qqpay', '0');
INSERT INTO `config` VALUES ('40', 'dmf_wepay_mchid', '');
INSERT INTO `config` VALUES ('41', 'dmf_alipay_mchid', '');
INSERT INTO `config` VALUES ('42', 'dmf_qqpay_mchid', '');
INSERT INTO `config` VALUES ('43', 'dmf_wepay_token', '');
INSERT INTO `config` VALUES ('44', 'dmf_alipay_token', '');
INSERT INTO `config` VALUES ('45', 'dmf_qqpay_token', '');
INSERT INTO `config` VALUES ('46', 'dmf_wepay_phone', '');
INSERT INTO `config` VALUES ('47', 'dmf_alipay_phone', '');
INSERT INTO `config` VALUES ('48', 'dmf_qqpay_phone', '');

CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_way` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付类型',
  `money` int(11) NOT NULL DEFAULT '0' COMMENT '充值金额，单位分',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '充值状态：1-成功',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `user` ADD `remember_token` VARCHAR(256) NULL DEFAULT '' AFTER `status`;


