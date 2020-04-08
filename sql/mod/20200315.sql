-- 加入昵称，修改email
ALTER TABLE  `user`
    DROP INDEX `unq_username`,
    CHANGE `username` `email` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
    ADD UNIQUE `unq_email` (`email`) USING BTREE,
    ADD COLUMN `username` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '昵称' AFTER `id`;

ALTER TABLE `verify_code` CHANGE `username` `address` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户邮箱';

-- 优惠券 sn值为唯一值
ALTER TABLE `coupon` ADD UNIQUE `unq_sn` (`sn`);

-- 加入支付 PayJS
insert into `config` VALUES ('99', 'payjs_mch_id', '');
insert into `config` VALUES ('100', 'payjs_key', '');

-- 运行 php artisan updateUserName 自动获取QQ用户昵称