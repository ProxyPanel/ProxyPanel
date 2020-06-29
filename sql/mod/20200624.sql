ALTER TABLE `user`
    CHANGE `uuid` `vmess_id` VARCHAR(64) NOT NULL DEFAULT '',
    ADD `protocol_param` varchar(255) DEFAULT '' COMMENT '协议参数'；