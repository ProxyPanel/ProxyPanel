ALTER TABLE `ticket`
    ADD `admin_id` INT(10) UNSIGNED DEFAULT '0' COMMENT '管理员ID' AFTER `user_id`;

ALTER TABLE `ticket_reply`
    ADD `admin_id` INT(10) UNSIGNED DEFAULT '0' COMMENT '管理员ID' AFTER `user_id`;

-- 升级sql后 运行 php artisan updateTicket 更新工单