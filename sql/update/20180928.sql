-- 节点是否启用检测
ALTER TABLE `ss_node`
ADD COLUMN `is_tcp_check` tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否开启检测: 0-不开启、1-开启' AFTER `ssh_port`;
