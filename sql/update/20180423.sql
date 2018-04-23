-- 订单表字段改名
ALTER TABLE `order`
	CHANGE COLUMN `totalOriginalPrice` `origin_amount` INT(11) NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分' AFTER `coupon_id`,
	CHANGE COLUMN `totalPrice` `amount` INT(11) NOT NULL DEFAULT '0' COMMENT '订单总价，单位分' AFTER `origin_amount`;



-- 订单商品表字段改名
ALTER TABLE `order_goods`
	CHANGE COLUMN `original_price` `origin_price` INT(11) NOT NULL DEFAULT '0' COMMENT '商品原价，单位分' AFTER `num`;
