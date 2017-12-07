INSERT INTO `country` VALUES ('40', '阿联酋', 'ae');
INSERT INTO `country` VALUES ('41', '南非', 'za');
INSERT INTO `country` VALUES ('42', '缅甸', 'mm');
INSERT INTO `country` VALUES ('43', '冰岛', 'is');
INSERT INTO `country` VALUES ('44', '芬兰', 'fi');
INSERT INTO `country` VALUES ('45', '卢森堡', 'lu');
INSERT INTO `country` VALUES ('46', '比利时', 'be');


-- 用户余额字段由decimal改为int，数值变大一百倍
ALTER TABLE `user` MODIFY `balance` int(11) NOT NULL DEFAULT '0' COMMENT '余额，单位分';
UPDATE `user` SET balance = balance * 100;


-- 用于余额日志表 balance 字段改 amount字段
ALTER TABLE `user_balance_log` CHANGE `balance` `amount` int(11) NOT NULL DEFAULT '0' COMMENT '发生金额，单位分';
