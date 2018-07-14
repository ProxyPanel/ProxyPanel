-- 用途可以多选
ALTER TABLE `user`
	CHANGE COLUMN `usage` `usage` VARCHAR(10) NOT NULL DEFAULT '4' COMMENT '用途：1-手机、2-电脑、3-路由器、4-其他' AFTER `qq`;
