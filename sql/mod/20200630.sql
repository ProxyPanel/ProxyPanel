ALTER TABLE `ss_node`
    CHANGE `ssh_port` `push_port` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '消息推送端口',
    ADD `tls_provider`            TEXT                 NULL     DEFAULT NULL COMMENT 'V2Ray节点的TLS提供商授权信息' AFTER `v2_tls`,
    DROP `v2_tls_insecure`,
    DROP `v2_tls_insecure_ciphers`;

CREATE TABLE `node_auth`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `node_id`    INT(10) UNSIGNED NOT NULL                          DEFAULT '0' COMMENT '授权节点ID',
    `key`        CHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT '认证KEY',
    `secret`     CHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  DEFAULT '' COMMENT '通信密钥',
    `created_at` DATETIME         NULL                              DEFAULT NULL COMMENT '创建时间',
    `updated_at` DATETIME         NULL                              DEFAULT NULL COMMENT '最后更新时间',
    PRIMARY KEY (`id`),
    INDEX `id` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT ='节点授权密钥表';

CREATE TABLE `node_certificate`
(
    `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `domain`     VARCHAR(255)     NOT NULL COMMENT '域名',
    `key`        TEXT             NULL COMMENT '域名证书KEY',
    `pem`        TEXT             NULL COMMENT '域名证书PEM',
    `created_at` DATETIME         NOT NULL,
    `updated_at` DATETIME         NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB COLLATE = 'utf8mb4_unicode_ci' COMMENT ='域名证书';

UPDATE `config` SET `name` = 'vnet_license' WHERE `config`.`id` = 51;
UPDATE `config` SET `name` = 'v2ray_license' WHERE `config`.`id` = 52;
UPDATE `config` SET `name` = 'trojan_license' WHERE `config`.`id` = 53;
