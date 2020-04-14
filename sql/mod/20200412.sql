rename table `email_log` to `notification_log`;
alter table `notification_log` COMMENT = '通知投递记录';
alter table `notification_log` CHANGE `type` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-ServerChan、3-Bark';
-- 加入Bark通知
insert into `config` VALUES ('104', 'bark_key', '');

update `config` SET `name` = 'is_node_offline' where `config`.`id` = 37;
update `config` SET `name` = 'is_notification', `value` = '0' where `config`.`id` = 39;
update `config` SET `name` = 'is_email_filtering',`value` = '0' where `config`.`id` = 58;
update `config` SET `name` = 'detection_check_times' where `config`.`id` = 68;
update `config` SET `name` = 'is_activate_account', `value` = '0' where `config`.`id` = 73;
update `config` SET `name` = 'offline_check_times', `value` = '10' where `config`.`id` = 98;