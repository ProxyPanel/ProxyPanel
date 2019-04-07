-- 使用软删除，移除is_del字段
ALTER TABLE `article`
	DROP COLUMN `is_del`;

ALTER TABLE `coupon`
	DROP COLUMN `is_del`;

ALTER TABLE `goods`
	DROP COLUMN `is_del`;

ALTER TABLE `invite`
	DROP COLUMN `is_del`;


ALTER TABLE `article`
	ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '删除时间' AFTER `updated_at`;

ALTER TABLE `coupon`
	ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '删除时间' AFTER `updated_at`;

ALTER TABLE `goods`
	ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '删除时间' AFTER `updated_at`;

ALTER TABLE `invite`
	ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '删除时间' AFTER `updated_at`;
