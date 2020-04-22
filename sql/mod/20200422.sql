-- 推送通知 方式重置
update `config` SET `name` = 'is_notification', `value` = '0' where `config`.`id` = 39;

-- 修改支付方式命名
alter table `order` CHANGE `pay_way` `pay_way` VARCHAR(20) NOT NULL DEFAULT '1' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等';
UPDATE `order` SET `pay_way`= 'balance' WHERE pay_way = '1';
UPDATE `order` SET `pay_way`= 'youzan' WHERE pay_way = '2';
UPDATE `order` SET `pay_way`= 'f2fpay' WHERE pay_way = '5';
