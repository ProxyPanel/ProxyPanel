-- 节点表增加防墙监测字段
ALTER TABLE `ss_node` ADD COLUMN `ssh_port` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '22' COMMENT 'SSH端口' AFTER `is_subscribe`;
ALTER TABLE `ss_node` ADD COLUMN `icmp` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'ICMP检测：-2-内外都不通、-1-内不通外通、0-外不通内通、1-内外都通'  AFTER `ssh_port`;
ALTER TABLE `ss_node` ADD COLUMN `tcp` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'TCP检测：-2-内外都不通、-1-内不通外通、0-外不通内通、1-内外都通' AFTER `icmp`;
ALTER TABLE `ss_node` ADD COLUMN `udp` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'ICMP检测：-2-内外都不通、-1-内不通外通、0-外不通内通、1-内外都通' AFTER `tcp`;

-- 优惠券操作日志加备注字段
ALTER TABLE `coupon_log` ADD COLUMN `desc` varchar(50) NOT NULL DEFAULT '' COMMENT '备注' AFTER `order_id`;