-- 商品加排序
ALTER TABLE `goods` ADD COLUMN `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序' AFTER `days`;