CREATE TABLE `level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL DEFAULT '1' COMMENT '等级',
  `level_name` varchar(100) NOT NULL DEFAULT '' COMMENT '等级名称',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `level` VALUES (1, '1', '倔强青铜', '2017-10-26 15:56:52', '2017-10-26 15:38:58');
INSERT INTO `level` VALUES (2, '2', '秩序白银', '2017-10-26 15:57:30', '2017-10-26 12:37:51');
INSERT INTO `level` VALUES (3, '3', '荣耀黄金', '2017-10-26 15:41:31', '2017-10-26 15:41:31');
INSERT INTO `level` VALUES (4, '4', '尊贵铂金', '2017-10-26 15:41:38', '2017-10-26 15:41:38');
INSERT INTO `level` VALUES (5, '5', '永恒钻石', '2017-10-26 15:41:47', '2017-10-26 15:41:47');
INSERT INTO `level` VALUES (6, '6', '至尊黑曜', '2017-10-26 15:41:56', '2017-10-26 15:41:56');
INSERT INTO `level` VALUES (7, '7', '最强王者', '2017-10-26 15:42:02', '2017-10-26 15:42:02');