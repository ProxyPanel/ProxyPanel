
ALTER TABLE `goods`
	ADD COLUMN `info` varchar(255) DEFAULT '' COMMENT '商品信息' AFTER `price`;

INSERT INTO `config` VALUES ('96', 'AppStore_id', 0);
INSERT INTO `config` VALUES ('97', 'AppStore_password', 0);
INSERT INTO `config` VALUES ('98', 'admin_email', '');
