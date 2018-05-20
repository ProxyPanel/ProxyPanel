-- 节点是否允许用户订阅
ALTER TABLE `ss_node` ADD COLUMN `is_subscribe` TINYINT(4) NULL DEFAULT '1' COMMENT '是否允许用户订阅该节点：0-否、1-是' AFTER `monitor_url`;