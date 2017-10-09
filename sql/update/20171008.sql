ALTER TABLE `goods` ADD COLUMN `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '商品类型：1-流量包、2-套餐' AFTER `score`;
ALTER TABLE `goods` ADD COLUMN `days` int(11) NOT NULL DEFAULT '30' COMMENT '有效期' AFTER `desc`;
ALTER TABLE `order_goods` ADD COLUMN `is_expire` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已过期：0-未过期、1-已过期' AFTER `price`;