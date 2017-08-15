# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.18)
# Database: 2
# Generation Time: 2017-07-29 06:28:10 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table ss_node
# ------------------------------------------------------------

CREATE TABLE `ss_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '名称',
  `server` varchar(128) NOT NULL DEFAULT '' COMMENT '服务器地址',
  `method` varchar(32) NOT NULL DEFAULT 'aes-192-ctr' COMMENT '加密方式',
  `custom_method` varchar(30) NOT NULL DEFAULT 'aes-192-ctr' COMMENT '自定义加密方式',
  `protocol` varchar(128) NOT NULL DEFAULT 'auth_chain_a' COMMENT '协议',
  `protocol_param` varchar(128) DEFAULT '' COMMENT '协议参数',
  `obfs` varchar(128) NOT NULL DEFAULT 'tls1.2_ticket_auth' COMMENT '混淆',
  `obfs_param` varchar(128) DEFAULT '' COMMENT '混淆参数',
  `traffic_rate` float NOT NULL DEFAULT '1' COMMENT '流量比率',
  `bandwidth` int(11) NOT NULL DEFAULT '100' COMMENT '出口带宽，单位M',
  `traffic` bigint(20) NOT NULL DEFAULT '1000' COMMENT '每月可用流量，单位G',
  `monitor_url` varchar(255) DEFAULT NULL COMMENT '监控地址',
  `compatible` tinyint(4) DEFAULT '0' COMMENT '兼容SS',
  `sort` int(3) NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠前显示',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0-维护、1-正常',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点信息表';



# Dump of table ss_node_info
# ------------------------------------------------------------

CREATE TABLE `ss_node_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '节点ID',
  `uptime` float NOT NULL COMMENT '更新时间',
  `load` varchar(32) NOT NULL COMMENT '负载',
  `log_time` int(11) NOT NULL COMMENT '记录时间',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='节点负载信息';



# Dump of table ss_node_online_log
# ------------------------------------------------------------

CREATE TABLE `ss_node_online_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_id` int(11) NOT NULL COMMENT '节点ID',
  `online_user` int(11) NOT NULL COMMENT '在线用户数',
  `log_time` int(11) NOT NULL COMMENT '记录时间',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='节点在线信息';



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '密码',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT 'SS端口',
  `passwd` varchar(32) NOT NULL DEFAULT '' COMMENT 'SS密码',
  `transfer_enable` bigint(20) NOT NULL DEFAULT '1073741824000' COMMENT '可用流量，单位字节，默认1TiB',
  `u` bigint(20) NOT NULL DEFAULT '0' COMMENT '已上传流量，单位字节',
  `d` bigint(20) NOT NULL DEFAULT '0' COMMENT '已下载流量，单位字节',
  `t` int(11) NOT NULL DEFAULT '0' COMMENT '最后使用时间',
  `enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  `method` varchar(30) NOT NULL DEFAULT 'aes-192-ctr' COMMENT '加密方式',
  `custom_method` varchar(30) DEFAULT 'aes-192-ctr' COMMENT '自定义加密方式',
  `protocol` varchar(30) NOT NULL DEFAULT 'auth_chain_a' COMMENT '协议',
  `protocol_param` varchar(255) DEFAULT '' COMMENT '协议参数',
  `obfs` varchar(30) NOT NULL DEFAULT 'tls1.2_ticket_auth' COMMENT '混淆',
  `obfs_param` varchar(255) DEFAULT '' COMMENT '混淆参数',
  `speed_limit_per_con` int(255) NOT NULL DEFAULT '204800' COMMENT '单连接限速，默认200M，单位KB',
  `speed_limit_per_user` int(255) NOT NULL DEFAULT '204800' COMMENT '单用户限速，默认200M，单位KB',
  `wechat` varchar(30) DEFAULT '' COMMENT '微信',
  `qq` varchar(20) DEFAULT '' COMMENT 'QQ',
  `usage` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用途：1-手机、2-电脑、3-路由器、4-其他',
  `pay_way` tinyint(4) NOT NULL DEFAULT '3' COMMENT '付费方式：1-月付、2-半年付、3-年付',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `enable_time` date DEFAULT NULL COMMENT '开通日期',
  `expire_time` date NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
  `remark` text COMMENT '备注',
  `is_admin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否管理员：0-否、1-是',
  `reg_ip` varchar(20) NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `port` (`port`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`id`, `username`, `password`, `port`, `passwd`, `transfer_enable`, `u`, `d`, `t`, `enable`, `method`, `custom_method`, `protocol`, `protocol_param`, `obfs`, `obfs_param`, `speed_limit_per_con`, `speed_limit_per_user`, `wechat`, `qq`, `usage`, `pay_way`, `balance`, `enable_time`, `expire_time`, `remark`, `is_admin`, `reg_ip`, `created_at`, `updated_at`)
VALUES (1,'admin','e10adc3949ba59abbe56e057f20f883e',10000,'@123',1073741824000,0,0,0,1,'aes-192-ctr','aes-192-ctr','auth_chain_a','','tls1.2_ticket_auth','',204800,204800,'','',1,3,0.00,NULL,'2099-01-01',NULL,1,'127.0.0.1',NULL,NULL);

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_traffic_log
# ------------------------------------------------------------

CREATE TABLE `user_traffic_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `u` int(11) NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` int(11) NOT NULL DEFAULT '0' COMMENT '下载流量',
  `node_id` int(11) NOT NULL DEFAULT '0' COMMENT '节点ID',
  `rate` float NOT NULL COMMENT '流量比例',
  `traffic` varchar(32) NOT NULL COMMENT '产生流量',
  `log_time` int(11) NOT NULL COMMENT '记录时间',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ss_config`
-- ----------------------------
DROP TABLE IF EXISTS `ss_config`;
CREATE TABLE `ss_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型：1-加密方式、2-协议、3-混淆',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认：0-不是、1-是',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序：值越大排越前',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '最后一次更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of ss_config
-- ----------------------------
INSERT INTO `ss_config` VALUES ('1', 'none', '1', '0', '0', '2017-08-01 13:12:23', '2017-08-01 13:12:23');
INSERT INTO `ss_config` VALUES ('2', 'rc4-md5', '1', '0', '0', '2017-08-01 13:12:29', '2017-08-01 13:12:29');
INSERT INTO `ss_config` VALUES ('3', 'bf-cfb', '1', '0', '0', '2017-08-01 13:13:05', '2017-08-01 13:13:05');
INSERT INTO `ss_config` VALUES ('4', 'aes-128-cfb', '1', '0', '0', '2017-08-01 13:13:13', '2017-08-01 13:13:13');
INSERT INTO `ss_config` VALUES ('5', 'aes-192-cfb', '1', '0', '0', '2017-08-01 13:13:25', '2017-08-01 13:13:25');
INSERT INTO `ss_config` VALUES ('6', 'aes-256-cfb', '1', '0', '0', '2017-08-01 13:13:39', '2017-08-01 13:13:39');
INSERT INTO `ss_config` VALUES ('7', 'aes-128-ctr', '1', '0', '0', '2017-08-01 13:13:46', '2017-08-01 13:13:46');
INSERT INTO `ss_config` VALUES ('8', 'aes-192-ctr', '1', '1', '0', '2017-08-01 13:13:53', '2017-08-01 13:13:53');
INSERT INTO `ss_config` VALUES ('9', 'aes-256-ctr', '1', '0', '0', '2017-08-01 13:14:00', '2017-08-01 13:14:00');
INSERT INTO `ss_config` VALUES ('10', 'camellia-128-cfb', '1', '0', '0', '2017-08-01 13:14:08', '2017-08-01 13:14:08');
INSERT INTO `ss_config` VALUES ('11', 'camellia-192-cfb', '1', '0', '0', '2017-08-01 13:14:12', '2017-08-01 13:14:12');
INSERT INTO `ss_config` VALUES ('12', 'camellia-256-cfb', '1', '0', '0', '2017-08-01 13:14:51', '2017-08-01 13:14:51');
INSERT INTO `ss_config` VALUES ('13', 'salsa20', '1', '0', '0', '2017-08-01 13:15:09', '2017-08-01 13:15:09');
INSERT INTO `ss_config` VALUES ('14', 'chacha20', '1', '0', '0', '2017-08-01 13:15:16', '2017-08-01 13:15:16');
INSERT INTO `ss_config` VALUES ('15', 'chacha20-ietf', '1', '0', '0', '2017-08-01 13:15:27', '2017-08-01 13:15:27');
INSERT INTO `ss_config` VALUES ('16', 'chacha20-ietf-poly1305', '1', '0', '0', '2017-08-01 13:15:39', '2017-08-01 13:15:39');
INSERT INTO `ss_config` VALUES ('17', 'chacha20-poly1305', '1', '0', '0', '2017-08-01 13:15:46', '2017-08-01 13:15:46');
INSERT INTO `ss_config` VALUES ('18', 'xchacha-ietf-poly1305', '1', '0', '0', '2017-08-01 13:21:51', '2017-08-01 13:21:51');
INSERT INTO `ss_config` VALUES ('19', 'aes-128-gcm', '1', '0', '0', '2017-08-01 13:22:05', '2017-08-01 13:22:05');
INSERT INTO `ss_config` VALUES ('20', 'aes-192-gcm', '1', '0', '0', '2017-08-01 13:22:12', '2017-08-01 13:22:12');
INSERT INTO `ss_config` VALUES ('21', 'aes-256-gcm', '1', '0', '0', '2017-08-01 13:22:19', '2017-08-01 13:22:19');
INSERT INTO `ss_config` VALUES ('22', 'sodium-aes-256-gcm', '1', '0', '0', '2017-08-01 13:22:32', '2017-08-01 13:22:32');
INSERT INTO `ss_config` VALUES ('23', 'origin', '2', '0', '0', '2017-08-01 13:23:57', '2017-08-01 13:23:57');
INSERT INTO `ss_config` VALUES ('24', 'auth_sha1_v4', '2', '0', '0', '2017-08-01 13:24:41', '2017-08-01 13:24:41');
INSERT INTO `ss_config` VALUES ('25', 'auth_aes128_md5', '2', '0', '0', '2017-08-01 13:24:58', '2017-08-01 13:24:58');
INSERT INTO `ss_config` VALUES ('26', 'auth_aes128_sha1', '2', '0', '0', '2017-08-01 13:25:11', '2017-08-01 13:25:11');
INSERT INTO `ss_config` VALUES ('27', 'auth_chain_a', '2', '1', '0', '2017-08-01 13:25:24', '2017-08-01 13:25:24');
INSERT INTO `ss_config` VALUES ('28', 'auth_chain_b', '2', '0', '0', '2017-08-01 14:02:31', '2017-08-01 14:02:31');
INSERT INTO `ss_config` VALUES ('29', 'plain', '3', '0', '0', '2017-08-01 13:29:14', '2017-08-01 13:29:14');
INSERT INTO `ss_config` VALUES ('30', 'http_simple', '3', '0', '0', '2017-08-01 13:29:30', '2017-08-01 13:29:30');
INSERT INTO `ss_config` VALUES ('31', 'http_post', '3', '0', '0', '2017-08-01 13:29:38', '2017-08-01 13:29:38');
INSERT INTO `ss_config` VALUES ('32', 'tls1.2_ticket_auth', '3', '1', '0', '2017-08-01 13:29:51', '2017-08-01 13:29:51');
INSERT INTO `ss_config` VALUES ('33', 'tls1.2_ticket_fastauth', '3', '0', '0', '2017-08-01 14:02:19', '2017-08-01 14:02:19');


-- ----------------------------
-- Table structure for `config`
-- ----------------------------
CREATE TABLE `config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置名',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES ('1', 'is_rand_port', 0);
INSERT INTO `config` VALUES ('2', 'is_user_rand_port', 0);


-- ----------------------------
-- Table structure for `article`
-- ----------------------------
CREATE TABLE `article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '文章标题',
  `content` text COMMENT '文章内容',
  `is_del` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
