ALTER TABLE `ss_node`
    CHANGE `is_nat` `is_ddns` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否使用DDNS：0-否、1-是';