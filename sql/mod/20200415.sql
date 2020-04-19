update `config` SET `name` = 'codepay_url', `value` = 'https://codepay.fateqq.com/creat_order/?' where `config`.`id` = 43;
update `config` SET `name` = 'codepay_id', `value` = '' where `config`.`id` = 44;
update `config` SET `name` = 'codepay_key', `value` = '' where `config`.`id` = 45;
update `config` SET `name` = 'website_callback_url', `value` = '' where `config`.`id` = 50;

alter table `payment`
  drop `order_sn`,
  drop `pay_way`,
  drop `qr_id`,
  drop `qr_url`,
  drop `qr_code`,
  drop `qr_local_url`;

drop table `order_goods`

ALTER TABLE `payment`
    ADD COLUMN `qr_code` text COLLATE utf8mb4_unicode_ci COMMENT '支付二维码' AFTER `amount`;