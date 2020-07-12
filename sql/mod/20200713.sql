-- 请选择性 更新本文件！ 以往ssrpanel使用label（标签）来区分用户的，可以使用本文件清理label, 再升级为用level（等级）区分的新版本
-- Optional update! For original ssrpanel update Label to Level as new way to rank the user-node.

DROP TABLE IF EXISTS `ss_node_label`;
CREATE TABLE `ss_node_label`
(
    `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '节点ID',
    `label_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签ID',
    PRIMARY KEY (`id`),
    INDEX `idx_node_label` (`node_id`, `label_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点标签';

-- 更新sql之后 | After sql update
-- 运行升级命令前， 请先给商品【套餐】加上等级，再运行 php artisan updateUserLevel 来更新等级； 别忘了同样要给节点加等级 和 标签！
-- Before apply level, set up goods in the level your want in the goods edition page, then run 'php artisan updateUserLevel' to update Level; Don't forget add level and label to nodes afterward.