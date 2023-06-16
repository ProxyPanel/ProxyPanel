/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-文章、2-站内公告、3-站外公告',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `language` char(5) NOT NULL DEFAULT 'zh_CN' COMMENT '语言',
  `category` varchar(255) DEFAULT NULL COMMENT '分组名',
  `logo` varchar(255) DEFAULT NULL COMMENT 'LOGO',
  `content` text COMMENT '内容',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `name` varchar(255) NOT NULL COMMENT '配置名',
  `value` text COMMENT '配置值',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `code` char(2) NOT NULL COMMENT 'ISO国家代码',
  `name` varchar(10) NOT NULL COMMENT '名称',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='国家代码';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '优惠券名称',
  `logo` varchar(255) DEFAULT NULL COMMENT '优惠券LOGO',
  `sn` varchar(50) NOT NULL COMMENT '优惠券码',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-抵用券、2-折扣券、3-充值券',
  `usable_times` smallint(5) unsigned DEFAULT NULL COMMENT '可使用次数',
  `value` int(10) unsigned NOT NULL COMMENT '折扣金额(元)/折扣力度',
  `limit` json DEFAULT NULL COMMENT '使用限制',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '使用权重, 高者优先',
  `start_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '有效期开始',
  `end_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '有效期结束',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) unsigned DEFAULT NULL COMMENT '优惠券ID',
  `goods_id` int(10) unsigned DEFAULT NULL COMMENT '商品ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '订单ID',
  `description` varchar(50) DEFAULT NULL COMMENT '备注',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `coupon_log_coupon_id_foreign` (`coupon_id`),
  KEY `coupon_log_goods_id_foreign` (`goods_id`),
  KEY `coupon_log_order_id_foreign` (`order_id`),
  CONSTRAINT `coupon_log_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`) ON DELETE SET NULL,
  CONSTRAINT `coupon_log_goods_id_foreign` FOREIGN KEY (`goods_id`) REFERENCES `goods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `coupon_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='优惠券使用日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_filter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-黑名单、2-白名单',
  `words` varchar(50) NOT NULL COMMENT '敏感词',
  PRIMARY KEY (`id`),
  KEY `email_filter_words_type_index` (`words`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='敏感词';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '商品名称',
  `logo` varchar(255) DEFAULT NULL COMMENT '商品图片地址',
  `traffic` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '商品内含多少流量，单位MiB',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '商品类型：1-流量包、2-套餐',
  `price` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '售价，单位分',
  `level` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '购买后给用户授权的等级',
  `category_id` int(11) DEFAULT 1 COMMENT '分类ID',
  `renew` int(10) unsigned DEFAULT NULL COMMENT '流量重置价格，单位分',
  `period` int(10) unsigned DEFAULT NULL COMMENT '流量自动重置周期',
  `info` varchar(255) DEFAULT NULL COMMENT '商品信息',
  `description` varchar(255) DEFAULT NULL COMMENT '商品描述',
  `days` int(10) unsigned NOT NULL DEFAULT '30' COMMENT '有效期',
  `invite_num` int(10) unsigned DEFAULT NULL COMMENT '赠送邀请码数',
  `limit_num` int(10) unsigned DEFAULT NULL COMMENT '限购数量，默认为null不限购',
  `speed_limit` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '商品限速',
  `color` varchar(50) NOT NULL DEFAULT 'green' COMMENT '商品颜色',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热销：0-否、1-是',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-下架、1-上架',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `goods_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '分类名称',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 0：隐藏 1：显示',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inviter_id` int(10) unsigned DEFAULT NULL COMMENT '邀请ID',
  `invitee_id` int(10) unsigned DEFAULT NULL COMMENT '受邀ID',
  `code` char(12) NOT NULL COMMENT '邀请码',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '邀请码状态：0-未使用、1-已使用、2-已过期',
  `dateline` datetime NOT NULL COMMENT '有效期至',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_code_unique` (`code`),
  KEY `invite_inviter_id_foreign` (`inviter_id`),
  KEY `invite_invitee_id_foreign` (`invitee_id`),
  CONSTRAINT `invite_invitee_id_foreign` FOREIGN KEY (`invitee_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invite_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请码表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='标签';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `label_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `label_node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `label_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_label_node_id_label_id_unique` (`node_id`,`label_id`),
  KEY `idx_node_label` (`node_id`,`label_id`),
  KEY `node_label_label_id_foreign` (`label_id`),
  CONSTRAINT `node_label_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`) ON DELETE CASCADE,
  CONSTRAINT `node_label_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点标签';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` tinyint(3) unsigned NOT NULL COMMENT '等级',
  `name` varchar(100) NOT NULL COMMENT '等级名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_level_unique` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='等级表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marketing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marketing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型：1-邮件群发',
  `receiver` text NOT NULL COMMENT '接收者',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `error` varchar(255) DEFAULT NULL COMMENT '错误信息',
  `status` tinyint(1) NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet',
  `name` varchar(128) NOT NULL COMMENT '名称',
  `country_code` char(5) NOT NULL DEFAULT 'un' COMMENT '国家代码',
  `server` varchar(255) DEFAULT NULL COMMENT '服务器域名地址',
  `ip` text COMMENT '服务器IPV4地址',
  `ipv6` text COMMENT '服务器IPV6地址',
  `level` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '等级：0-无等级，全部可见',
  `rule_group_id` int(10) unsigned DEFAULT NULL COMMENT '从属规则分组ID',
  `speed_limit` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '节点限速，为0表示不限速，单位Byte',
  `client_limit` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '设备数限制',
  `description` varchar(255) DEFAULT NULL COMMENT '节点简单描述',
  `profile` json NOT NULL COMMENT '节点设置选项',
  `geo` varchar(255) DEFAULT NULL COMMENT '节点地理位置',
  `traffic_rate` double(6,2) unsigned NOT NULL DEFAULT '1.00' COMMENT '流量比率',
  `is_display` tinyint(4) NOT NULL DEFAULT '3' COMMENT '节点显示模式：0-不显示、1-只页面、2-只订阅、3-都可',
  `is_ddns` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否使用DDNS：0-否、1-是',
  `relay_node_id` int(10) unsigned DEFAULT NULL COMMENT '中转节点对接母节点, 默认NULL',
  `is_udp` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用UDP：0-不启用、1-启用',
  `push_port` smallint(5) unsigned NOT NULL DEFAULT '1000' COMMENT '消息推送端口',
  `detection_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部',
  `port` smallint(5) unsigned DEFAULT NULL COMMENT '单端口的端口号或连接端口号',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序值，值越大越靠前显示',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-维护、1-正常',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `node_type_index` (`type`),
  KEY `node_rule_group_id_foreign` (`rule_group_id`),
  CONSTRAINT `node_rule_group_id_foreign` FOREIGN KEY (`rule_group_id`) REFERENCES `rule_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL COMMENT '授权节点ID',
  `key` char(16) NOT NULL COMMENT '认证KEY',
  `secret` char(8) NOT NULL COMMENT '通信密钥',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `node_auth_node_id_foreign` (`node_id`),
  CONSTRAINT `node_auth_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点授权密钥表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_certificate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_certificate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL COMMENT '域名',
  `key` text COMMENT '域名证书KEY',
  `pem` text COMMENT '域名证书PEM',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_certificate_domain_unique` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='域名证书';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_daily_data_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_daily_data_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL COMMENT '节点ID',
  `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '总流量',
  `traffic` varchar(255) DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `node_daily_data_flow_node_id_index` (`node_id`),
  CONSTRAINT `node_daily_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_heartbeat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_heartbeat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `uptime` int(10) unsigned NOT NULL COMMENT '后端存活时长，单位秒',
  `load` varchar(255) NOT NULL COMMENT '负载',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `node_heartbeat_node_id_index` (`node_id`),
  CONSTRAINT `node_heartbeat_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点心跳信息';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_hourly_data_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_hourly_data_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '总流量',
  `traffic` varchar(255) DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `node_hourly_data_flow_node_id_index` (`node_id`),
  CONSTRAINT `node_hourly_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_online_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_online_ip` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `port` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '端口',
  `type` char(3) NOT NULL DEFAULT 'tcp' COMMENT '类型：all、tcp、udp',
  `ip` text COMMENT '连接IP：每个IP用,号隔开',
  `created_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '上报时间',
  PRIMARY KEY (`id`),
  KEY `node_online_ip_node_id_index` (`node_id`),
  KEY `node_online_ip_user_id_index` (`user_id`),
  KEY `node_online_ip_port_index` (`port`),
  CONSTRAINT `node_online_ip_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE,
  CONSTRAINT `node_online_ip_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_online_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_online_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL COMMENT '节点ID',
  `online_user` int(10) unsigned NOT NULL COMMENT '在线用户数',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `node_online_log_node_id_index` (`node_id`),
  CONSTRAINT `node_online_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='节点在线信息';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `node_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int(10) unsigned NOT NULL COMMENT '节点ID',
  `user_group_id` int(10) unsigned NOT NULL COMMENT '从属用户分组ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_user_group_user_group_id_node_id_unique` (`user_group_id`,`node_id`),
  KEY `node_user_group_node_id_foreign` (`node_id`),
  CONSTRAINT `node_user_group_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE,
  CONSTRAINT `node_user_group_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg_id` char(36) DEFAULT NULL COMMENT '消息对公查询号',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-邮件、2-ServerChan、3-Bark、4-Telegram',
  `address` varchar(255) NOT NULL COMMENT '收信地址',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：-1发送失败、0-等待发送、1-发送成功',
  `error` text COMMENT '发送失败抛出的异常信息',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知投递记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(20) NOT NULL COMMENT '订单编号',
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者ID',
  `goods_id` int(10) unsigned DEFAULT NULL COMMENT '商品ID',
  `coupon_id` int(10) unsigned DEFAULT NULL COMMENT '优惠券ID',
  `origin_amount` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单原始总价，单位分',
  `amount` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单总价，单位分',
  `expired_at` datetime DEFAULT NULL COMMENT '过期时间',
  `is_expire` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已过期：0-未过期、1-已过期',
  `pay_type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal',
  `pay_way` varchar(10) NOT NULL DEFAULT 'balance' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_search` (`user_id`,`goods_id`,`is_expire`,`status`),
  KEY `order_goods_id_foreign` (`goods_id`),
  KEY `order_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `order_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_goods_id_foreign` FOREIGN KEY (`goods_id`) REFERENCES `goods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(64) NOT NULL COMMENT '支付单号（本地订单号）',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '本地订单ID',
  `amount` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '金额，单位分',
  `qr_code` text COMMENT '支付二维码',
  `url` text COMMENT '支付链接',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '支付状态：-1-支付失败、0-等待支付、1-支付成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `payment_user_id_order_id_index` (`user_id`,`order_id`),
  KEY `payment_order_id_foreign` (`order_id`),
  CONSTRAINT `payment_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_callback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_callback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(64) NOT NULL COMMENT '本地订单号',
  `out_trade_no` varchar(64) NOT NULL COMMENT '外部订单号（支付平台）',
  `amount` int(10) unsigned NOT NULL COMMENT '交易金额，单位分',
  `status` tinyint(1) NOT NULL COMMENT '交易状态：0-失败、1-成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付回调日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referral_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '申请者ID',
  `before` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '操作前可提现金额，单位分',
  `after` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '操作后可提现金额，单位分',
  `amount` int(10) unsigned NOT NULL COMMENT '本次提现金额，单位分',
  `link_logs` json NOT NULL COMMENT '关联返利日志ID，例如：1,3,4',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `referral_apply_user_id_foreign` (`user_id`),
  CONSTRAINT `referral_apply_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现申请';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referral_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invitee_id` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `inviter_id` int(10) unsigned NOT NULL COMMENT '推广人ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '关联订单ID',
  `amount` int(10) unsigned NOT NULL COMMENT '消费金额，单位分',
  `commission` int(10) unsigned NOT NULL COMMENT '返利金额',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-未提现、1-审核中、2-已提现',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `referral_log_invitee_id_foreign` (`invitee_id`),
  KEY `referral_log_order_id_foreign` (`order_id`),
  KEY `referral_log_inviter_id_invitee_id_index` (`inviter_id`,`invitee_id`),
  CONSTRAINT `referral_log_invitee_id_foreign` FOREIGN KEY (`invitee_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `referral_log_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referral_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消费返利日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-正则表达式、2-域名、3-IP、4-协议',
  `name` varchar(100) NOT NULL COMMENT '规则描述',
  `pattern` text NOT NULL COMMENT '规则值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='审计规则';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rule_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rule_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '模式：1-阻断、0-放行',
  `name` varchar(255) NOT NULL COMMENT '分组名称',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='审计规则分组';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rule_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rule_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '触发者ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID',
  `rule_id` int(10) unsigned DEFAULT 0 COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址',
  `reason` varchar(255) DEFAULT NULL COMMENT '触发原因',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx` (`user_id`,`node_id`,`rule_id`),
  KEY `rule_log_node_id_foreign` (`node_id`),
  KEY `rule_log_rule_id_foreign` (`rule_id`),
  CONSTRAINT `rule_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rule_log_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rule_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='触发审计规则日志表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rule_rule_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rule_rule_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL COMMENT '规则ID',
  `rule_group_id` int(10) unsigned NOT NULL COMMENT '从属规则分组ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_rule_group_rule_group_id_rule_id_unique` (`rule_group_id`,`rule_id`),
  KEY `rule_rule_group_rule_id_foreign` (`rule_id`),
  CONSTRAINT `rule_rule_group_rule_group_id_foreign` FOREIGN KEY (`rule_group_id`) REFERENCES `rule_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rule_rule_group_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ss_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ss_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '配置名',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '类型：1-加密方式、2-协议、3-混淆',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认：0-不是、1-是',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '排序：值越大排越前',
  PRIMARY KEY (`id`),
  KEY `ss_config_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `admin_id` int(10) unsigned DEFAULT NULL COMMENT '管理员ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-待处理、1-已处理未关闭、2-已关闭',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `ticket_user_id_foreign` (`user_id`),
  KEY `ticket_admin_id_foreign` (`admin_id`),
  CONSTRAINT `ticket_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL COMMENT '工单ID',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `admin_id` int(10) unsigned DEFAULT NULL COMMENT '管理员ID',
  `content` text NOT NULL COMMENT '回复内容',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `ticket_reply_user_id_foreign` (`user_id`),
  KEY `ticket_reply_admin_id_foreign` (`admin_id`),
  KEY `ticket_reply_ticket_id_foreign` (`ticket_id`),
  CONSTRAINT `ticket_reply_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_reply_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_reply_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(64) NOT NULL COMMENT '昵称',
  `username` varchar(128) NOT NULL COMMENT '邮箱',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `port` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '代理端口',
  `passwd` varchar(16) NOT NULL COMMENT '代理密码',
  `vmess_id` char(36) NOT NULL,
  `transfer_enable` bigint(20) unsigned NOT NULL DEFAULT '1099511627776' COMMENT '可用流量，单位字节，默认1TiB',
  `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '已上传流量，单位字节',
  `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '已下载流量，单位字节',
  `t` int(10) unsigned DEFAULT NULL COMMENT '最后使用时间',
  `ip` varchar(45) DEFAULT NULL COMMENT '最后连接IP',
  `enable` tinyint(1) NOT NULL DEFAULT 1 COMMENT '代理状态',
  `method` varchar(30) NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
  `protocol` varchar(30) NOT NULL DEFAULT 'origin' COMMENT '协议',
  `protocol_param` varchar(255) DEFAULT NULL COMMENT '协议参数',
  `obfs` varchar(30) NOT NULL DEFAULT 'plain' COMMENT '混淆',
  `speed_limit` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '用户限速，为0表示不限速，单位Byte',
  `wechat` varchar(30) DEFAULT NULL COMMENT '微信',
  `qq` varchar(20) DEFAULT NULL COMMENT 'QQ',
  `credit` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '余额，单位分',
  `expired_at` date NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
  `ban_time` int(10) unsigned DEFAULT NULL COMMENT '封禁到期时间',
  `remark` text COMMENT '备注',
  `level` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '等级，默认0级',
  `user_group_id` int(10) unsigned DEFAULT NULL COMMENT '所属分组',
  `reg_ip` varchar(45) NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
  `last_login` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `inviter_id` int(10) unsigned DEFAULT NULL COMMENT '邀请人',
  `reset_time` date DEFAULT NULL COMMENT '流量重置日期',
  `invite_num` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '可生成邀请码数',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：-1-禁用、0-未激活、1-正常',
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_username_unique` (`username`),
  KEY `idx_search` (`enable`,`status`,`port`),
  KEY `user_inviter_id_foreign` (`inviter_id`),
  KEY `user_user_group_id_foreign` (`user_group_id`),
  CONSTRAINT `user_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_user_group_id_foreign` FOREIGN KEY (`user_group_id`) REFERENCES `user_group` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_baned_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_baned_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '封禁账号时长，单位分钟',
  `description` varchar(255) DEFAULT NULL COMMENT '操作描述',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-未处理、1-已处理',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `user_baned_log_user_id_foreign` (`user_id`),
  CONSTRAINT `user_baned_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户封禁日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_credit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_credit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '订单ID',
  `before` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '发生前余额，单位分',
  `after` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '发生后金额，单位分',
  `amount` int(11) NOT NULL DEFAULT 0 COMMENT '发生金额，单位分',
  `description` varchar(255) DEFAULT NULL COMMENT '操作描述',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_credit_log_user_id_foreign` (`user_id`),
  KEY `user_credit_log_order_id_foreign` (`order_id`),
  CONSTRAINT `user_credit_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_credit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_daily_data_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_daily_data_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID，null表示统计全部节点',
  `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '总流量',
  `traffic` varchar(255) DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_node` (`user_id`,`node_id`),
  KEY `user_daily_data_flow_node_id_foreign` (`node_id`),
  CONSTRAINT `user_daily_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_daily_data_flow_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_data_modify_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_data_modify_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '发生的订单ID',
  `before` bigint(20) NOT NULL DEFAULT 0 COMMENT '操作前流量',
  `after` bigint(20) NOT NULL DEFAULT 0 COMMENT '操作后流量',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_data_modify_log_user_id_foreign` (`user_id`),
  KEY `user_data_modify_log_order_id_foreign` (`order_id`),
  CONSTRAINT `user_data_modify_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_data_modify_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户流量变动日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '分组名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户分组控制表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_hourly_data_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_hourly_data_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID，null表示统计全部节点',
  `u` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '总流量',
  `traffic` varchar(255) DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_node` (`user_id`,`node_id`),
  KEY `user_hourly_data_flow_node_id_foreign` (`node_id`),
  CONSTRAINT `user_hourly_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_hourly_data_flow_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `ip` varchar(45) NOT NULL COMMENT 'IP地址',
  `country` varchar(128) NOT NULL COMMENT '国家',
  `province` varchar(128) NOT NULL COMMENT '省份',
  `city` varchar(128) NOT NULL COMMENT '城市',
  `county` varchar(128) NOT NULL COMMENT '郡县',
  `isp` varchar(128) NOT NULL COMMENT '运营商',
  `area` varchar(255) NOT NULL COMMENT '地区',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_login_log_user_id_foreign` (`user_id`),
  CONSTRAINT `user_login_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_oauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(10) NOT NULL COMMENT '登录类型',
  `identifier` varchar(128) NOT NULL COMMENT '手机号/邮箱/第三方的唯一标识',
  `credential` varchar(128) DEFAULT NULL COMMENT '密码/Token凭证',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_oauth_user_id_type_unique` (`user_id`,`type`),
  UNIQUE KEY `user_oauth_identifier_unique` (`identifier`),
  CONSTRAINT `user_oauth_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_subscribe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `code` char(8) NOT NULL COMMENT '订阅地址唯一识别码',
  `times` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '地址请求次数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用、1-启用',
  `ban_time` int(10) unsigned DEFAULT NULL COMMENT '封禁时间',
  `ban_desc` text COMMENT '封禁理由',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_subscribe_code_unique` (`code`),
  KEY `user_id` (`user_id`,`status`),
  KEY `user_subscribe_code_index` (`code`),
  CONSTRAINT `user_subscribe_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_subscribe_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_subscribe_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_subscribe_id` int(10) unsigned NOT NULL COMMENT '对应user_subscribe的id',
  `request_ip` varchar(45) DEFAULT NULL COMMENT '请求IP',
  `request_time` datetime NOT NULL COMMENT '请求时间',
  `request_header` text COMMENT '请求头部信息',
  PRIMARY KEY (`id`),
  KEY `user_subscribe_log_user_subscribe_id_index` (`user_subscribe_id`),
  CONSTRAINT `user_subscribe_log_user_subscribe_id_foreign` FOREIGN KEY (`user_subscribe_id`) REFERENCES `user_subscribe` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_traffic_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_traffic_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `node_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `u` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '上传流量',
  `d` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '下载流量',
  `rate` double(6,2) unsigned NOT NULL COMMENT '倍率',
  `traffic` varchar(32) NOT NULL COMMENT '产生流量',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_node_time` (`user_id`,`node_id`,`log_time`),
  KEY `user_traffic_log_node_id_foreign` (`node_id`),
  CONSTRAINT `user_traffic_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `node` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_traffic_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `verify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '激活类型：1-自行激活、2-管理员激活',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `token` varchar(32) NOT NULL COMMENT '校验token',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `verify_user_id_foreign` (`user_id`),
  CONSTRAINT `verify_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `verify_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verify_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(128) NOT NULL COMMENT '用户邮箱',
  `code` char(6) NOT NULL COMMENT '验证码',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='注册激活验证码';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` VALUES (2,'2020_08_21_145711_create_article_table',1);
INSERT INTO `migrations` VALUES (3,'2020_08_21_145711_create_config_table',1);
INSERT INTO `migrations` VALUES (4,'2020_08_21_145711_create_country_table',1);
INSERT INTO `migrations` VALUES (5,'2020_08_21_145711_create_coupon_log_table',1);
INSERT INTO `migrations` VALUES (6,'2020_08_21_145711_create_coupon_table',1);
INSERT INTO `migrations` VALUES (7,'2020_08_21_145711_create_email_filter_table',1);
INSERT INTO `migrations` VALUES (8,'2020_08_21_145711_create_failed_jobs_table',1);
INSERT INTO `migrations` VALUES (9,'2020_08_21_145711_create_goods_table',1);
INSERT INTO `migrations` VALUES (10,'2020_08_21_145711_create_invite_table',1);
INSERT INTO `migrations` VALUES (11,'2020_08_21_145711_create_jobs_table',1);
INSERT INTO `migrations` VALUES (12,'2020_08_21_145711_create_label_table',1);
INSERT INTO `migrations` VALUES (13,'2020_08_21_145711_create_level_table',1);
INSERT INTO `migrations` VALUES (14,'2020_08_21_145711_create_marketing_table',1);
INSERT INTO `migrations` VALUES (15,'2020_08_21_145711_create_node_auth_table',1);
INSERT INTO `migrations` VALUES (16,'2020_08_21_145711_create_node_certificate_table',1);
INSERT INTO `migrations` VALUES (17,'2020_08_21_145711_create_node_daily_data_flow_table',1);
INSERT INTO `migrations` VALUES (18,'2020_08_21_145711_create_node_hourly_data_flow_table',1);
INSERT INTO `migrations` VALUES (19,'2020_08_21_145711_create_node_label_table',1);
INSERT INTO `migrations` VALUES (20,'2020_08_21_145711_create_node_rule_table',1);
INSERT INTO `migrations` VALUES (21,'2020_08_21_145711_create_notification_log_table',1);
INSERT INTO `migrations` VALUES (22,'2020_08_21_145711_create_order_table',1);
INSERT INTO `migrations` VALUES (23,'2020_08_21_145711_create_payment_callback_table',1);
INSERT INTO `migrations` VALUES (24,'2020_08_21_145711_create_payment_table',1);
INSERT INTO `migrations` VALUES (25,'2020_08_21_145711_create_referral_apply_table',1);
INSERT INTO `migrations` VALUES (26,'2020_08_21_145711_create_referral_log_table',1);
INSERT INTO `migrations` VALUES (27,'2020_08_21_145711_create_rule_group_node_table',1);
INSERT INTO `migrations` VALUES (28,'2020_08_21_145711_create_rule_group_table',1);
INSERT INTO `migrations` VALUES (29,'2020_08_21_145711_create_rule_log_table',1);
INSERT INTO `migrations` VALUES (30,'2020_08_21_145711_create_rule_table',1);
INSERT INTO `migrations` VALUES (31,'2020_08_21_145711_create_ss_config_table',1);
INSERT INTO `migrations` VALUES (32,'2020_08_21_145711_create_ss_node_info_table',1);
INSERT INTO `migrations` VALUES (33,'2020_08_21_145711_create_ss_node_ip_table',1);
INSERT INTO `migrations` VALUES (34,'2020_08_21_145711_create_ss_node_online_log_table',1);
INSERT INTO `migrations` VALUES (35,'2020_08_21_145711_create_ss_node_table',1);
INSERT INTO `migrations` VALUES (36,'2020_08_21_145711_create_ticket_reply_table',1);
INSERT INTO `migrations` VALUES (37,'2020_08_21_145711_create_ticket_table',1);
INSERT INTO `migrations` VALUES (38,'2020_08_21_145711_create_user_baned_log_table',1);
INSERT INTO `migrations` VALUES (39,'2020_08_21_145711_create_user_credit_log_table',1);
INSERT INTO `migrations` VALUES (40,'2020_08_21_145711_create_user_daily_data_flow_table',1);
INSERT INTO `migrations` VALUES (41,'2020_08_21_145711_create_user_data_modify_log_table',1);
INSERT INTO `migrations` VALUES (42,'2020_08_21_145711_create_user_group_table',1);
INSERT INTO `migrations` VALUES (43,'2020_08_21_145711_create_user_hourly_data_flow_table',1);
INSERT INTO `migrations` VALUES (44,'2020_08_21_145711_create_user_login_log_table',1);
INSERT INTO `migrations` VALUES (45,'2020_08_21_145711_create_user_subscribe_log_table',1);
INSERT INTO `migrations` VALUES (46,'2020_08_21_145711_create_user_subscribe_table',1);
INSERT INTO `migrations` VALUES (47,'2020_08_21_145711_create_user_table',1);
INSERT INTO `migrations` VALUES (48,'2020_08_21_145711_create_user_traffic_log_table',1);
INSERT INTO `migrations` VALUES (49,'2020_08_21_145711_create_verify_code_table',1);
INSERT INTO `migrations` VALUES (50,'2020_08_21_145711_create_verify_table',1);
INSERT INTO `migrations` VALUES (51,'2020_09_24_184434_add_strip_config',1);
INSERT INTO `migrations` VALUES (52,'2020_10_11_000217_add_ddns_to_config_table',1);
INSERT INTO `migrations` VALUES (53,'2020_11_06_145018_create_permission_tables',1);
INSERT INTO `migrations` VALUES (54,'2020_11_10_075555_improve_table',1);
INSERT INTO `migrations` VALUES (55,'2020_12_07_120247_permission_data',1);
INSERT INTO `migrations` VALUES (56,'2020_12_24_074739_table_improvement',1);
INSERT INTO `migrations` VALUES (57,'2021_01_04_094946_drop_node_ping',1);
INSERT INTO `migrations` VALUES (58,'2021_01_04_172833_add-paybeaver-payment',1);
INSERT INTO `migrations` VALUES (59,'2021_01_15_065207_create_notifications_table',1);
INSERT INTO `migrations` VALUES (60,'2021_01_27_080544_config_clean',1);
INSERT INTO `migrations` VALUES (61,'2021_03_17_041036_add_aff_code_config',1);
INSERT INTO `migrations` VALUES (62,'2021_04_25_095012_ddns_node',1);
INSERT INTO `migrations` VALUES (63,'2021_05_16_215434_add_theadpay_payment',1);
INSERT INTO `migrations` VALUES (64,'2021_06_16_115448_oauth',1);
INSERT INTO `migrations` VALUES (65,'2021_06_23_103914_append_telegram_id_to_user_table',1);
INSERT INTO `migrations` VALUES (66,'2021_06_27_174304_append_v2_sni_to_node_table',1);
INSERT INTO `migrations` VALUES (67,'2021_07_13_190753_rm_telegram_in_user_table',1);
INSERT INTO `migrations` VALUES (68,'2021_07_23_151321_append_speed_limit_goods_table',1);
INSERT INTO `migrations` VALUES (69,'2021_07_24_214642_create_goods_category_table',1);
INSERT INTO `migrations` VALUES (70,'2021_07_25_124022_drop_v2_port',1);
INSERT INTO `migrations` VALUES (71,'2021_08_26_231620_more_notification',1);
INSERT INTO `migrations` VALUES (72,'2021_10_08_222109_add_payment_confirm_notification',1);
INSERT INTO `migrations` VALUES (73,'2021_11_25_211107_change_log_permission',1);
INSERT INTO `migrations` VALUES (74,'2022_01_16_160308_add_msgid_notification_log',1);
INSERT INTO `migrations` VALUES (75,'2022_01_22_231856_improve_node_table',1);
INSERT INTO `migrations` VALUES (76,'2022_08_04_001832_add_more_notifications',1);
INSERT INTO `migrations` VALUES (77,'2022_08_07_012002_modify_node_for_view',1);
INSERT INTO `migrations` VALUES (78,'2022_08_25_204229_improve_coupon',1);
INSERT INTO `migrations` VALUES (79,'2022_12_01_223612_add_options_to_article',1);
INSERT INTO `migrations` VALUES (80,'2023_01_04_210048_currency_internationalization',1);
INSERT INTO `migrations` VALUES (81,'2023_04_22_005731_change_subscribe_desc',1);
