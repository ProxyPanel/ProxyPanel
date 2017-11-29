
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

CREATE TABLE `ssrpanel`.`user_payment` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `money` FLOAT NOT NULL , `status` INT NOT NULL , PRIMARY KEY (`id`),  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL) ENGINE = InnoDB;
