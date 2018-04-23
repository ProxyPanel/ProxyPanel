ALTER TABLE `order` ADD COLUMN `goods_id`  int NOT NULL DEFAULT 0 COMMENT '商品ID' AFTER `user_id`;
ALTER TABLE `order` ADD COLUMN `expire_at` datetime DEFAULT NULL COMMENT '过期时间' AFTER `amount`;
ALTER TABLE `order` ADD COLUMN `is_expire`  tinyint NOT NULL DEFAULT 0 COMMENT '是否已过期：0-未过期、1-已过期' AFTER `expire_at`;
ALTER TABLE `order` ADD COLUMN `pay_way`  tinyint NOT NULL DEFAULT 0 COMMENT '支付方式：1-余额支付、2-PayPal' AFTER `is_expire`;

