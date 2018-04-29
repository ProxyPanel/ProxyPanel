-- 商品标签
CREATE TABLE `goods_label` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`goods_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
	`label_id` INT(11) NOT NULL DEFAULT '0' COMMENT '标签ID',
	PRIMARY KEY (`id`),
	INDEX `idx` (`goods_id`, `label_id`),
	INDEX `idx_goods_id` (`goods_id`),
	INDEX `idx_label_id` (`label_id`)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE='utf8mb4_unicode_ci' COMMENT='商品标签';
