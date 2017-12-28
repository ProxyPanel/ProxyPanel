-- 单端口多用户加入混淆字段
ALTER TABLE `ss_node`
ADD COLUMN `single_obfs`  varchar(50) NOT NULL DEFAULT '' COMMENT '混淆' AFTER `single_protocol`;

