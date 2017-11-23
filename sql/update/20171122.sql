
ALTER TABLE `ss_node` ADD COLUMN `country_code` char(5) DEFAULT '' COMMENT '国家代码' AFTER `group_id`;

-- ----------------------------
-- Table structure for `country`
-- ----------------------------
CREATE TABLE `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '代码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of country
-- ----------------------------
INSERT INTO `country` VALUES ('1', '澳大利亚', 'au');
INSERT INTO `country` VALUES ('2', '巴西', 'br');
INSERT INTO `country` VALUES ('3', '加拿大', 'ca');
INSERT INTO `country` VALUES ('4', '瑞士', 'ch');
INSERT INTO `country` VALUES ('5', '中国', 'cn');
INSERT INTO `country` VALUES ('6', '德国', 'de');
INSERT INTO `country` VALUES ('7', '丹麦', 'dk');
INSERT INTO `country` VALUES ('8', '埃及', 'eg');
INSERT INTO `country` VALUES ('9', '法国', 'fr');
INSERT INTO `country` VALUES ('10', '希腊', 'gr');
INSERT INTO `country` VALUES ('11', '香港', 'hk');
INSERT INTO `country` VALUES ('12', '印度尼西亚', 'id');
INSERT INTO `country` VALUES ('13', '爱尔兰', 'ie');
INSERT INTO `country` VALUES ('14', '以色列', 'il');
INSERT INTO `country` VALUES ('15', '印度', 'in');
INSERT INTO `country` VALUES ('16', '伊拉克', 'iq');
INSERT INTO `country` VALUES ('17', '伊朗', 'ir');
INSERT INTO `country` VALUES ('18', '意大利', 'it');
INSERT INTO `country` VALUES ('19', '日本', 'jp');
INSERT INTO `country` VALUES ('20', '韩国', 'kr');
INSERT INTO `country` VALUES ('21', '墨西哥', 'mx');
INSERT INTO `country` VALUES ('22', '马来西亚', 'my');
INSERT INTO `country` VALUES ('23', '荷兰', 'nl');
INSERT INTO `country` VALUES ('24', '挪威', 'no');
INSERT INTO `country` VALUES ('25', '纽西兰', 'nz');
INSERT INTO `country` VALUES ('26', '菲律宾', 'ph');
INSERT INTO `country` VALUES ('27', '俄罗斯', 'ru');
INSERT INTO `country` VALUES ('28', '瑞典', 'se');
INSERT INTO `country` VALUES ('29', '新加坡', 'sg');
INSERT INTO `country` VALUES ('30', '泰国', 'th');
INSERT INTO `country` VALUES ('31', '土耳其', 'tr');
INSERT INTO `country` VALUES ('32', '台湾', 'tw');
INSERT INTO `country` VALUES ('33', '英国', 'uk');
INSERT INTO `country` VALUES ('34', '美国', 'us');
INSERT INTO `country` VALUES ('35', '越南', 'vn');
INSERT INTO `country` VALUES ('36', '波兰', 'pl');
INSERT INTO `country` VALUES ('37', '哈萨克斯坦', 'kz');
INSERT INTO `country` VALUES ('38', '乌克兰', 'ua');
INSERT INTO `country` VALUES ('39', '罗马尼亚', 'ro');
INSERT INTO `country` VALUES ('40', '阿拉伯联合酋长国', 'ae');

