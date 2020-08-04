-- 适配V2Ray上报IP、VNET单端口上报IP
ALTER TABLE `ss_node_ip`
	ADD COLUMN `user_id` INT NOT NULL DEFAULT '0' COMMENT '用户ID' AFTER `node_id`;

-- 适配用队列发送邮件时邮件的状态
ALTER TABLE `email_log`
	CHANGE COLUMN `status` `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '状态：-1发送失败、0-等待发送、1-发送成功' AFTER `content`;

-- 原本状态为0的表示失败，现在都改为-1表示失败
UPDATE `email_log` SET `status` = -1 WHERE `status` = 0;