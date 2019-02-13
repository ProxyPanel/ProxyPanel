-- 适配vnet，修正限速字段，默认限速为10M
ALTER TABLE `user`
	CHANGE COLUMN `speed_limit_per_con` `speed_limit_per_con` BIGINT NOT NULL DEFAULT '10737418240' COMMENT '单连接限速，默认10M，为0表示不限速，单位Byte' AFTER `obfs_param`,
	CHANGE COLUMN `speed_limit_per_user` `speed_limit_per_user` BIGINT NOT NULL DEFAULT '10737418240' COMMENT '单用户限速，默认10M，为0表示不限速，单位Byte' AFTER `speed_limit_per_con`;
