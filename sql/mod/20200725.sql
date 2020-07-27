ALTER TABLE `ss_node`
    ADD `geo` VARCHAR(255) NULL DEFAULT NULL COMMENT '节点地理位置' AFTER `description`;

ALTER TABLE `user`
    ADD `group_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属分组' AFTER `level`;

CREATE TABLE `user_group`
(
    `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(255) DEFAULT NULL COMMENT '分组名称',
    `nodes` TEXT COMMENT '关联的节点ID，多个用,号分隔',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='用户分组控制表';