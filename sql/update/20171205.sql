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
