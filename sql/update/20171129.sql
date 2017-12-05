
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'wepay_enabled', '0');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'alipay_enabled', '0');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'qqpay_enabled', '0');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_wepay_mchid', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_alipay_mchid', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_qqpay_mchid', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_wepay_token', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_alipay_token', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_qqpay_token', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_wepay_phone', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_alipay_phone', '');
INSERT INTO `config` (`id`, `name`, `value`) VALUES (NULL, 'payment_qqpay_phone', '');

CREATE TABLE `ssrpanel`.`user_payment` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `user_id` INT NOT NULL ,
  `money` FLOAT NOT NULL ,
  `status` INT NOT NULL ,
  PRIMARY KEY (`id`),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE = InnoDB;

INSERT INTO `country` VALUES ('40', '阿联酋', 'ae');
INSERT INTO `country` VALUES ('41', '南非', 'za');
INSERT INTO `country` VALUES ('42', '缅甸', 'mm');
INSERT INTO `country` VALUES ('43', '冰岛', 'is');
INSERT INTO `country` VALUES ('44', '芬兰', 'fi');
INSERT INTO `country` VALUES ('45', '卢森堡', 'lu');
INSERT INTO `country` VALUES ('46', '比利时', 'be');


-- 用户余额字段由decimal改为int，数值变大一百倍
ALTER TABLE `user` MODIFY `balance` int(11) NOT NULL DEFAULT '0' COMMENT '余额，单位分';
UPDATE `user` SET balance = balance * 100;


-- 用于余额日志表 balance 字段改 amount字段
ALTER TABLE `user_balance_log` CHANGE `balance` `amount` int(11) NOT NULL DEFAULT '0' COMMENT '发生金额，单位分';
