-- 推送通知 方式重置
update `config` SET `name` = 'is_notification', `value` = '0' where `config`.`id` = 39;