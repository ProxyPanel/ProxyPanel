-- 添加各类支付方式首选项
update `config` SET `name` = 'is_AliPay', `value` = '' where `config`.`id` = 78;
update `config` SET `name` = 'is_QQPay', `value` = '' where `config`.`id` = 79;
update `config` SET `name` = 'is_WeChatPay', `value` = '' where `config`.`id` = 80;

-- 添加 支付订单命称
update `config` SET `name` = 'subject_name' where `config`.`id` = 91;