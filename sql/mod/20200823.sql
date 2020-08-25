ALTER TABLE `country`
    CHANGE `logo` `logo` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '优惠券LOGO';

ALTER TABLE `coupon_log`
    CHANGE `description` `description` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注';

ALTER TABLE `goods`
    CHANGE `renew` `renew`           INT(10) UNSIGNED DEFAULT NULL COMMENT '流量重置价格，单位分',
    CHANGE `period` `period`         INT(10) UNSIGNED DEFAULT NULL COMMENT '流量自动重置周期',
    CHANGE `invite_num` `invite_num` INT(10) UNSIGNED DEFAULT NULL COMMENT '赠送邀请码数',
    CHANGE `limit_num` `limit_num`   INT(10) UNSIGNED DEFAULT NULL COMMENT '限购数量，默认为null不限购';

ALTER TABLE `invite`
    CHANGE `code` `code` CHAR(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邀请码';

ALTER TABLE `order`
    CHANGE `goods_id` `goods_id`   INT(10) UNSIGNED DEFAULT NULL COMMENT '商品ID',
    CHANGE `coupon_id` `coupon_id` INT(10) UNSIGNED DEFAULT NULL COMMENT '优惠券ID';

ALTER TABLE `rule_log`
    CHANGE `reason` `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '触发原因';

ALTER TABLE `ss_node`
    CHANGE `v2_host` `v2_host` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'V2Ray伪装的域名',
    CHANGE `v2_path` `v2_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'V2Ray的WS/H2路径';

ALTER TABLE `user_credit_log`
    CHANGE `order_id`   `order_id` int(10) unsigned DEFAULT NULL COMMENT '订单ID',
    CHANGE `v2_path` `v2_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'V2Ray的WS/H2路径';

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (1, '2020_08_21_145711_create_article_table', 1),
       (2, '2020_08_21_145711_create_config_table', 1),
       (3, '2020_08_21_145711_create_country_table', 1),
       (4, '2020_08_21_145711_create_coupon_log_table', 1),
       (5, '2020_08_21_145711_create_coupon_table', 1),
       (6, '2020_08_21_145711_create_email_filter_table', 1),
       (7, '2020_08_21_145711_create_failed_jobs_table', 1),
       (8, '2020_08_21_145711_create_goods_table', 1),
       (9, '2020_08_21_145711_create_invite_table', 1),
       (10, '2020_08_21_145711_create_jobs_table', 1),
       (11, '2020_08_21_145711_create_label_table', 1),
       (12, '2020_08_21_145711_create_level_table', 1),
       (13, '2020_08_21_145711_create_marketing_table', 1),
       (14, '2020_08_21_145711_create_node_auth_table', 1),
       (15, '2020_08_21_145711_create_node_certificate_table', 1),
       (16, '2020_08_21_145711_create_node_daily_data_flow_table', 1),
       (17, '2020_08_21_145711_create_node_hourly_data_flow_table', 1),
       (18, '2020_08_21_145711_create_node_label_table', 1),
       (19, '2020_08_21_145711_create_node_ping_table', 1),
       (20, '2020_08_21_145711_create_node_rule_table', 1),
       (21, '2020_08_21_145711_create_notification_log_table', 1),
       (22, '2020_08_21_145711_create_order_table', 1),
       (23, '2020_08_21_145711_create_payment_callback_table', 1),
       (24, '2020_08_21_145711_create_payment_table', 1),
       (25, '2020_08_21_145711_create_products_pool_table', 1),
       (26, '2020_08_21_145711_create_referral_apply_table', 1),
       (27, '2020_08_21_145711_create_referral_log_table', 1),
       (28, '2020_08_21_145711_create_rule_group_node_table', 1),
       (29, '2020_08_21_145711_create_rule_group_table', 1),
       (30, '2020_08_21_145711_create_rule_log_table', 1),
       (31, '2020_08_21_145711_create_rule_table', 1),
       (32, '2020_08_21_145711_create_ss_config_table', 1),
       (33, '2020_08_21_145711_create_ss_node_info_table', 1),
       (34, '2020_08_21_145711_create_ss_node_ip_table', 1),
       (35, '2020_08_21_145711_create_ss_node_online_log_table', 1),
       (36, '2020_08_21_145711_create_ss_node_table', 1),
       (37, '2020_08_21_145711_create_ticket_reply_table', 1),
       (38, '2020_08_21_145711_create_ticket_table', 1),
       (39, '2020_08_21_145711_create_user_baned_log_table', 1),
       (40, '2020_08_21_145711_create_user_credit_log_table', 1),
       (41, '2020_08_21_145711_create_user_daily_data_flow_table', 1),
       (42, '2020_08_21_145711_create_user_data_modify_log_table', 1),
       (43, '2020_08_21_145711_create_user_group_table', 1),
       (44, '2020_08_21_145711_create_user_hourly_data_flow_table', 1),
       (45, '2020_08_21_145711_create_user_login_log_table', 1),
       (46, '2020_08_21_145711_create_user_subscribe_log_table', 1),
       (47, '2020_08_21_145711_create_user_subscribe_table', 1),
       (48, '2020_08_21_145711_create_user_table', 1),
       (49, '2020_08_21_145711_create_user_traffic_log_table', 1),
       (50, '2020_08_21_145711_create_verify_code_table', 1),
       (51, '2020_08_21_145711_create_verify_table', 1),
       (52, '2020_08_21_150711_preset_data', 1);
