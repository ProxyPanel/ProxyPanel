INSERT INTO `config` VALUES ('107', 'paypal_username', '');
INSERT INTO `config` VALUES ('108', 'paypal_password', '');
INSERT INTO `config` VALUES ('109', 'paypal_secret', '');
INSERT INTO `config` VALUES ('110', 'paypal_certificate', '');
INSERT INTO `config` VALUES ('111', 'paypal_app_id', '');

ALTER TABLE `payment`
    ADD COLUMN `url` text COLLATE utf8mb4_unicode_ci COMMENT '支付链接' AFTER `qr_code`;