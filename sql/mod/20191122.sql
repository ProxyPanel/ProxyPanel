ALTER TABLE `ss_node`
    CHANGE `is_tcp_check` `detectionType` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部';
UPDATE `config`
SET `name`  = 'nodes_detection',
    `value` = '0'
WHERE id = 67;
UPDATE `config`
SET `name`  = 'numberOfWarningTimes',
    `value` = '3'
WHERE id = 68;
ALTER TABLE `coupon`
    ADD COLUMN `rule` bigint(20) NOT NULL DEFAULT '0' COMMENT '使用限制，单位分' AFTER `discount`;