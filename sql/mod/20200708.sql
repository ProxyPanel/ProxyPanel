ALTER TABLE `ss_node`
    CHANGE `client_limit` `client_limit` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '设备数限制';

UPDATE `config` SET `name` = 'web_api_url' WHERE `config`.`id` = 51;
UPDATE `config` SET `name` = 'v2ray_tls_provider' WHERE `config`.`id` = 54;