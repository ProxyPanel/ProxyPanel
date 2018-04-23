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
ALTER TABLE `order_goods` MODIFY `origin_price` int(11) NOT NULL DEFAULT '0' COMMENT '商品原价，单位分';
UPDATE `order_goods` SET `origin_price` = `origin_price` * 100;

ALTER TABLE `order_goods` MODIFY `price` int(11) NOT NULL DEFAULT '0' COMMENT '商品实际价格，单位分';
UPDATE `order_goods` SET `price` = `price` * 100;

-- 订单表字段类型调整
ALTER TABLE `order` MODIFY `origin_amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分';
UPDATE `order` SET `origin_amount` = `origin_amount` * 100;

ALTER TABLE `order` MODIFY `amount` int(11) NOT NULL DEFAULT '0' COMMENT '订单总价，单位分';
UPDATE `order` SET `amount` = `amount` * 100;
