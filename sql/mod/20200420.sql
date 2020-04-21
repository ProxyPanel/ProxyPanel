-- 添加各类支付方式首选项
update `config` SET `name` = 'is_otherPay', `value` = '' where `config`.`id` = 81;

-- 添加麻瓜宝配置
update `config` SET `name` = 'bitpay_secret', `value` = '' where `config`.`id` = 86;