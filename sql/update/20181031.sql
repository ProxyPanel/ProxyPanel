-- 商品表加入是否限购字段
ALTER TABLE `goods`
	ADD COLUMN `is_limit` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否限购：0-否、1-是' AFTER `sort`;