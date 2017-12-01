-- 消费返利日志表字段类型调整
ALTER TABLE `referral_log` MODIFY `amount` int(11) NOT NULL DEFAULT '0' COMMENT '消费金额，单位分';
UPDATE `referral_log` SET `amount` = `amount` * 100;

-- 消费提现申请表字段类型调整
ALTER TABLE `referral_apply` MODIFY `before` int(11) NOT NULL DEFAULT '0' COMMENT '操作前可提现金额，单位分';
UPDATE `referral_apply` SET `before` = `before` * 100;

ALTER TABLE `referral_apply` MODIFY `after` int(11) NOT NULL DEFAULT '0' COMMENT '操作后可提现金额，单位分';
UPDATE `referral_apply` SET `after` = `after` * 100;

ALTER TABLE `referral_apply` MODIFY `amount` int(11) NOT NULL DEFAULT '0' COMMENT '本次提现金额，单位分';
UPDATE `referral_apply` SET `amount` = `amount` * 100;

-- 订单商品表字段类型调整
ALTER TABLE `order_goods` MODIFY `original_price` int(11) NOT NULL DEFAULT '0' COMMENT '商品原价，单位分';
UPDATE `order_goods` SET `original_price` = `original_price` * 100;

ALTER TABLE `order_goods` MODIFY `price` int(11) NOT NULL DEFAULT '0' COMMENT '商品实际价格，单位分';
UPDATE `order_goods` SET `price` = `price` * 100;

-- 订单表字段类型调整
ALTER TABLE `order` MODIFY `totalOriginalPrice` int(11) NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分';
UPDATE `order` SET `totalOriginalPrice` = `totalOriginalPrice` * 100;

ALTER TABLE `order` MODIFY `totalPrice` int(11) NOT NULL DEFAULT '0' COMMENT '订单总价，单位分';
UPDATE `order` SET `totalPrice` = `totalPrice` * 100;
