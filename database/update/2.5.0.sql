-- 2.4.0版本以前 或 数据库以下字段为bit的 需要运行本文件来矫正数据库

alter table `goods`
    change `is_hot` `is_hot` TINYINT(1) not null default 0 comment '是否热销：0-否、1-是',
    change `status` `status` TINYINT(1) not null default 1 comment '状态：0-下架、1-上架';

alter table `node_rule`
    change `is_black` `is_black` TINYINT(1) not null default 1 comment '是否黑名单模式：0-不是、1-是';

alter table `order`
    change `is_expire` `is_expire` TINYINT(1) not null default 0 comment '是否已过期：0-未过期、1-已过期';

alter table `payment_callback`
    change `status` `status` TINYINT(1) not null comment '交易状态：0-失败、1-成功';

alter table `products_pool`
    change `status` `status` TINYINT(1) not null default 1 comment '状态：0-未启用、1-已启用';

alter table `rule_group`
    change `type` `type` TINYINT(1) not null default 1 comment '模式：1-阻断、0-放行';

alter table `ss_config`
    change `is_default` `is_default` TINYINT(1) not null default 0 comment '是否默认：0-不是、1-是';

alter table `ss_node`
    change `is_subscribe` `is_subscribe` TINYINT(1) not null default 1 comment '是否允许用户订阅该节点：0-否、1-是',
    change `is_ddns` `is_ddns`           TINYINT(1) not null default 0 comment '是否使用DDNS：0-否、1-是',
    change `is_relay` `is_relay`         TINYINT(1) not null default 0 comment '是否中转节点：0-否、1-是',
    change `is_udp` `is_udp`             TINYINT(1) not null default 1 comment '是否启用UDP：0-不启用、1-启用',
    change `compatible` `compatible`     TINYINT(1) not null default 0 comment '兼容SS',
    change `single` `single`             TINYINT(1) not null default 0 comment '启用单端口功能：0-否、1-是',
    change `status` `status`             TINYINT(1) not null default 1 comment '状态：0-维护、1-正常',
    change `v2_tls` `v2_tls`             TINYINT(1) not null default 0 comment 'V2Ray连接TLS：0-未开启、1-开启';

alter table `user`
    change `is_admin` `is_admin` TINYINT(1) not null default 0 comment '是否管理员：0-否、1-是';

alter table `user_baned_log`
    change `status` `status` TINYINT(1) not null default 0 comment '状态：0-未处理、1-已处理';

alter table `user_subscribe`
    change `status` `status` TINYINT(1) not null default 1 comment '状态：0-禁用、1-启用';
