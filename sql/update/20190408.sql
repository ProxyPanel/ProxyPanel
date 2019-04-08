-- 加入中转节点，废弃无用的is_udp字段
ALTER TABLE `ss_node`
  DROP COLUMN `is_udp`;

ALTER TABLE `ss_node`
  ADD COLUMN `is_transit` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否中转节点：0-否、1-是' AFTER `is_nat`;
