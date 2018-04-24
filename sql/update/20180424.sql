-- 订单编号改名
ALTER TABLE `order`
	CHANGE COLUMN `orderId` `order_sn` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '订单编号' COLLATE 'utf8mb4_unicode_ci' AFTER `oid`;

ALTER TABLE `payment`
	CHANGE COLUMN `orderId` `order_sn` VARCHAR(50) NULL DEFAULT NULL COMMENT '本地订单长ID' COLLATE 'utf8mb4_unicode_ci' AFTER `oid`;

ALTER TABLE `order_goods`
	CHANGE COLUMN `orderId` `order_sn` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '订单编号' AFTER `oid`;
