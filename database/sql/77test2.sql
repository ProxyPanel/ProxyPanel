-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2020-12-31 15:10:00
-- 服务器版本： 5.6.49-log
-- PHP Version: 7.2.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `77test2`
--

-- --------------------------------------------------------

--
-- 表的结构 `article`
--

CREATE TABLE IF NOT EXISTS `article` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-文章、2-站内公告、3-站外公告',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `summary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '简介',
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'LOGO',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '配置值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO国家代码',
  `name` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `coupon`
--

CREATE TABLE IF NOT EXISTS `coupon` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '优惠券名称',
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '优惠券LOGO',
  `sn` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '优惠券码',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-抵用券、2-折扣券、3-充值券',
  `usable_times` smallint(5) unsigned DEFAULT NULL COMMENT '可使用次数',
  `value` int(10) unsigned NOT NULL COMMENT '折扣金额(元)/折扣力度',
  `rule` int(10) unsigned DEFAULT NULL COMMENT '使用限制(元)',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效期开始',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效期结束',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `coupon_log`
--

CREATE TABLE IF NOT EXISTS `coupon_log` (
  `id` int(10) unsigned NOT NULL,
  `coupon_id` int(10) unsigned DEFAULT NULL COMMENT '优惠券ID',
  `goods_id` int(10) unsigned DEFAULT NULL COMMENT '商品ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '订单ID',
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `email_filter`
--

CREATE TABLE IF NOT EXISTS `email_filter` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-黑名单、2-白名单',
  `words` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '敏感词'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `failed_jobs`
--

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `goods`
--

CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品名称',
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品图片地址',
  `traffic` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '商品内含多少流量，单位MiB',
  `usage` int(6) NOT NULL DEFAULT '2',
  `speed` int(6) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '商品类型：1-流量包、2-套餐',
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '售价，单位分',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '购买后给用户授权的等级',
  `renew` int(10) unsigned DEFAULT NULL COMMENT '流量重置价格，单位分',
  `period` int(10) unsigned DEFAULT NULL COMMENT '流量自动重置周期',
  `info` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品信息',
  `desc` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品描述',
  `days` int(10) unsigned NOT NULL DEFAULT '30' COMMENT '有效期',
  `invite_num` int(10) unsigned DEFAULT NULL COMMENT '赠送邀请码数',
  `limit_num` int(10) unsigned DEFAULT NULL COMMENT '限购数量，默认为null不限购',
  `color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'green' COMMENT '商品颜色',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否热销：0-否、1-是',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-下架、1-上架',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `invite`
--

CREATE TABLE IF NOT EXISTS `invite` (
  `id` int(10) unsigned NOT NULL,
  `inviter_id` int(10) unsigned DEFAULT NULL COMMENT '邀请ID',
  `invitee_id` int(10) unsigned DEFAULT NULL COMMENT '受邀ID',
  `code` char(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邀请码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邀请码状态：0-未使用、1-已使用、2-已过期',
  `dateline` datetime NOT NULL COMMENT '有效期至',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `label`
--

CREATE TABLE IF NOT EXISTS `label` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `level`
--

CREATE TABLE IF NOT EXISTS `level` (
  `id` int(10) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL COMMENT '等级',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '等级名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `marketing`
--

CREATE TABLE IF NOT EXISTS `marketing` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '类型：1-邮件群发',
  `receiver` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收者',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `error` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '错误信息',
  `status` tinyint(1) NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `model_has_permissions`
--

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `model_has_roles`
--

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_auth`
--

CREATE TABLE IF NOT EXISTS `node_auth` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL COMMENT '授权节点ID',
  `key` char(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '认证KEY',
  `secret` char(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通信密钥',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_certificate`
--

CREATE TABLE IF NOT EXISTS `node_certificate` (
  `id` int(10) unsigned NOT NULL,
  `domain` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '域名',
  `key` text COLLATE utf8mb4_unicode_ci COMMENT '域名证书KEY',
  `pem` text COLLATE utf8mb4_unicode_ci COMMENT '域名证书PEM',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_daily_data_flow`
--

CREATE TABLE IF NOT EXISTS `node_daily_data_flow` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `u` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_hourly_data_flow`
--

CREATE TABLE IF NOT EXISTS `node_hourly_data_flow` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `u` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_label`
--

CREATE TABLE IF NOT EXISTS `node_label` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `label_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_ping`
--

CREATE TABLE IF NOT EXISTS `node_ping` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应节点id',
  `ct` int(11) NOT NULL DEFAULT '0' COMMENT '电信',
  `cu` int(11) NOT NULL DEFAULT '0' COMMENT '联通',
  `cm` int(11) NOT NULL DEFAULT '0' COMMENT '移动',
  `hk` int(11) NOT NULL DEFAULT '0' COMMENT '香港',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `node_rule`
--

CREATE TABLE IF NOT EXISTS `node_rule` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID',
  `rule_id` int(10) unsigned DEFAULT NULL COMMENT '审计规则ID',
  `is_black` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否黑名单模式：0-不是、1-是',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `notification_log`
--

CREATE TABLE IF NOT EXISTS `notification_log` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-邮件、2-ServerChan、3-Bark、4-Telegram',
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收信地址',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：-1发送失败、0-等待发送、1-发送成功',
  `error` text COLLATE utf8mb4_unicode_ci COMMENT '发送失败抛出的异常信息',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `oauth_access_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `oauth_auth_codes`
--

CREATE TABLE IF NOT EXISTS `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `oauth_clients`
--

CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `oauth_personal_access_clients`
--

CREATE TABLE IF NOT EXISTS `oauth_personal_access_clients` (
  `id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `oauth_refresh_tokens`
--

CREATE TABLE IF NOT EXISTS `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `order`
--

CREATE TABLE IF NOT EXISTS `order` (
  `id` int(10) unsigned NOT NULL,
  `order_sn` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单编号',
  `user_id` int(10) unsigned NOT NULL COMMENT '操作人',
  `goods_id` int(10) unsigned DEFAULT NULL COMMENT '商品ID',
  `coupon_id` int(10) unsigned DEFAULT NULL COMMENT '优惠券ID',
  `origin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单原始总价，单位分',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单总价，单位分',
  `expired_at` datetime DEFAULT NULL COMMENT '过期时间',
  `is_expire` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已过期：0-未过期、1-已过期',
  `pay_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal',
  `pay_way` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'balance' COMMENT '支付方式：balance、f2fpay、codepay、payjs、bitpayx等',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(10) unsigned NOT NULL,
  `trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付单号（本地订单号）',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '本地订单ID',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额，单位分',
  `pay_secret` text COLLATE utf8mb4_unicode_ci COMMENT '支付二维码',
  `url` text COLLATE utf8mb4_unicode_ci COMMENT '支付链接',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态：-1-支付失败、0-等待支付、1-支付成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `payment_callback`
--

CREATE TABLE IF NOT EXISTS `payment_callback` (
  `id` int(10) unsigned NOT NULL,
  `trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '本地订单号',
  `out_trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外部订单号（支付平台）',
  `amount` int(10) unsigned NOT NULL COMMENT '交易金额，单位分',
  `status` tinyint(1) NOT NULL COMMENT '交易状态：0-失败、1-成功',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `products_pool`
--

CREATE TABLE IF NOT EXISTS `products_pool` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `min_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '适用最小金额，单位分',
  `max_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '适用最大金额，单位分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-未启用、1-已启用',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `referral_apply`
--

CREATE TABLE IF NOT EXISTS `referral_apply` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `before` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作前可提现金额，单位分',
  `after` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作后可提现金额，单位分',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '本次提现金额，单位分',
  `link_logs` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关联返利日志ID，例如：1,3,4',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：-1-驳回、0-待审核、1-审核通过待打款、2-已打款',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `referral_log`
--

CREATE TABLE IF NOT EXISTS `referral_log` (
  `id` int(10) unsigned NOT NULL,
  `invitee_id` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `inviter_id` int(10) unsigned NOT NULL COMMENT '推广人ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '关联订单ID',
  `amount` int(10) unsigned NOT NULL COMMENT '消费金额，单位分',
  `commission` int(10) unsigned NOT NULL COMMENT '返利金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-未提现、1-审核中、2-已提现',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `role_has_permissions`
--

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule`
--

CREATE TABLE IF NOT EXISTS `rule` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-正则表达式、2-域名、3-IP、4-协议',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则描述',
  `pattern` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule_group`
--

CREATE TABLE IF NOT EXISTS `rule_group` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '模式：1-阻断、0-放行',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT '关联的规则ID，多个用,号分隔',
  `nodes` text COLLATE utf8mb4_unicode_ci COMMENT '关联的节点ID，多个用,号分隔',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule_group_node`
--

CREATE TABLE IF NOT EXISTS `rule_group_node` (
  `id` int(10) unsigned NOT NULL,
  `rule_group_id` int(10) unsigned NOT NULL COMMENT '规则分组ID',
  `node_id` int(10) unsigned NOT NULL COMMENT '节点ID',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `rule_log`
--

CREATE TABLE IF NOT EXISTS `rule_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID',
  `rule_id` int(10) unsigned DEFAULT '0' COMMENT '规则ID，0表示白名单模式下访问访问了非规则允许的网址',
  `reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '触发原因',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ss_config`
--

CREATE TABLE IF NOT EXISTS `ss_config` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1-加密方式、2-协议、3-混淆',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认：0-不是、1-是',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序：值越大排越前'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ss_node`
--

CREATE TABLE IF NOT EXISTS `ss_node` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `country_code` char(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'un' COMMENT '国家代码',
  `server` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '服务器域名地址',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '服务器IPV4地址',
  `ipv6` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '服务器IPV6地址',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级：0-无等级，全部可见',
  `speed_limit` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '节点限速，为0表示不限速，单位Byte',
  `client_limit` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '设备数限制',
  `relay_server` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '中转地址',
  `relay_port` smallint(5) unsigned DEFAULT NULL COMMENT '中转端口',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '节点简单描述',
  `geo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '节点地理位置',
  `method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
  `protocol` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'origin' COMMENT '协议',
  `protocol_param` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '协议参数',
  `obfs` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'plain' COMMENT '混淆',
  `obfs_param` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '混淆参数',
  `traffic_rate` double(6,2) unsigned NOT NULL DEFAULT '1.00' COMMENT '流量比率',
  `is_subscribe` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许用户订阅该节点：0-否、1-是',
  `is_ddns` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用DDNS：0-否、1-是',
  `is_relay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否中转节点：0-否、1-是',
  `is_udp` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用UDP：0-不启用、1-启用',
  `push_port` smallint(5) unsigned NOT NULL DEFAULT '1000' COMMENT '消息推送端口',
  `detection_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '节点检测: 0-关闭、1-只检测TCP、2-只检测ICMP、3-检测全部',
  `compatible` tinyint(1) NOT NULL DEFAULT '0' COMMENT '兼容SS',
  `single` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用单端口功能：0-否、1-是',
  `port` smallint(5) unsigned DEFAULT NULL COMMENT '单端口的端口号或连接端口号',
  `passwd` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '单端口的连接密码',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序值，值越大越靠前显示',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-维护、1-正常',
  `v2_alter_id` smallint(5) unsigned NOT NULL DEFAULT '16' COMMENT 'V2Ray额外ID',
  `v2_port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'V2Ray服务端口',
  `v2_method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aes-128-gcm' COMMENT 'V2Ray加密方式',
  `v2_net` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tcp' COMMENT 'V2Ray传输协议',
  `v2_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'V2Ray伪装类型',
  `v2_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'V2Ray伪装的域名',
  `v2_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'V2Ray的WS/H2路径',
  `v2_tls` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'V2Ray连接TLS：0-未开启、1-开启',
  `tls_provider` text COLLATE utf8mb4_unicode_ci COMMENT 'V2Ray节点的TLS提供商授权信息',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ss_node_info`
--

CREATE TABLE IF NOT EXISTS `ss_node_info` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `uptime` int(10) unsigned NOT NULL COMMENT '后端存活时长，单位秒',
  `load` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '负载',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ss_node_ip`
--

CREATE TABLE IF NOT EXISTS `ss_node_ip` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '端口',
  `type` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tcp' COMMENT '类型：all、tcp、udp',
  `ip` text COLLATE utf8mb4_unicode_ci COMMENT '连接IP：每个IP用,号隔开',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ss_node_online_log`
--

CREATE TABLE IF NOT EXISTS `ss_node_online_log` (
  `id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL COMMENT '节点ID',
  `online_user` int(10) unsigned NOT NULL COMMENT '在线用户数',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ticket`
--

CREATE TABLE IF NOT EXISTS `ticket` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int(10) unsigned DEFAULT NULL COMMENT '管理员ID',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-待处理、1-已处理未关闭、2-已关闭',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `ticket_reply`
--

CREATE TABLE IF NOT EXISTS `ticket_reply` (
  `id` int(10) unsigned NOT NULL,
  `ticket_id` int(10) unsigned NOT NULL COMMENT '工单ID',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `admin_id` int(10) unsigned DEFAULT NULL COMMENT '管理员ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回复内容',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL,
  `username` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '昵称',
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
  `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '代理端口',
  `passwd` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '代理密码',
  `vmess_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer_enable` bigint(20) unsigned NOT NULL DEFAULT '1099511627776' COMMENT '可用流量，单位字节，默认1TiB',
  `u` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已上传流量，单位字节',
  `d` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已下载流量，单位字节',
  `t` int(10) unsigned DEFAULT NULL COMMENT '最后使用时间',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '最后连接IP',
  `enable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '代理状态',
  `method` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aes-256-cfb' COMMENT '加密方式',
  `protocol` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'origin' COMMENT '协议',
  `protocol_param` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '协议参数',
  `obfs` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'plain' COMMENT '混淆',
  `speed_limit` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户限速，为0表示不限速，单位Byte',
  `wechat` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '微信',
  `qq` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'QQ',
  `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '余额，单位分',
  `expired_at` date NOT NULL DEFAULT '2099-01-01' COMMENT '过期时间',
  `ban_time` int(10) unsigned DEFAULT NULL COMMENT '封禁到期时间',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '等级，默认0级',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属分组',
  `reg_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
  `last_login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `inviter_id` int(10) unsigned DEFAULT NULL COMMENT '邀请人',
  `reset_time` date DEFAULT NULL COMMENT '流量重置日期',
  `invite_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可生成邀请码数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：-1-禁用、0-未激活、1-正常',
  `remember_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_baned_log`
--

CREATE TABLE IF NOT EXISTS `user_baned_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '封禁账号时长，单位分钟',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作描述',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-未处理、1-已处理',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_credit_log`
--

CREATE TABLE IF NOT EXISTS `user_credit_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '账号ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '订单ID',
  `before` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发生前余额，单位分',
  `after` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发生后金额，单位分',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '发生金额，单位分',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '操作描述',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_daily_data_flow`
--

CREATE TABLE IF NOT EXISTS `user_daily_data_flow` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID，null表示统计全部节点',
  `u` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_data_modify_log`
--

CREATE TABLE IF NOT EXISTS `user_data_modify_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` int(10) unsigned DEFAULT NULL COMMENT '发生的订单ID',
  `before` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作前流量',
  `after` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作后流量',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
  `nodes` text COLLATE utf8mb4_unicode_ci COMMENT '关联的节点ID，多个用,号分隔'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_hourly_data_flow`
--

CREATE TABLE IF NOT EXISTS `user_hourly_data_flow` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `node_id` int(10) unsigned DEFAULT NULL COMMENT '节点ID，null表示统计全部节点',
  `u` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '下载流量',
  `total` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总流量',
  `traffic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '总流量（带单位）',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_login_log`
--

CREATE TABLE IF NOT EXISTS `user_login_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP地址',
  `country` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '国家',
  `province` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '省份',
  `city` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '城市',
  `county` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '郡县',
  `isp` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '运营商',
  `area` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '地区',
  `app_key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `device` tinyint(4) NOT NULL COMMENT '1,web  2,andriod 3,ios 4,ipad 5,windows 6,mac',
  `created_at` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_subscribe`
--

CREATE TABLE IF NOT EXISTS `user_subscribe` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `code` char(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订阅地址唯一识别码',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '地址请求次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0-禁用、1-启用',
  `ban_time` int(10) unsigned DEFAULT NULL COMMENT '封禁时间',
  `ban_desc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '封禁理由',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_subscribe_log`
--

CREATE TABLE IF NOT EXISTS `user_subscribe_log` (
  `id` int(10) unsigned NOT NULL,
  `user_subscribe_id` int(10) unsigned NOT NULL COMMENT '对应user_subscribe的id',
  `request_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '请求IP',
  `request_time` datetime NOT NULL COMMENT '请求时间',
  `request_header` text COLLATE utf8mb4_unicode_ci COMMENT '请求头部信息'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_traffic_log`
--

CREATE TABLE IF NOT EXISTS `user_traffic_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `node_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '节点ID',
  `u` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传流量',
  `d` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载流量',
  `rate` double(6,2) unsigned NOT NULL COMMENT '倍率',
  `traffic` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '产生流量',
  `log_time` int(10) unsigned NOT NULL COMMENT '记录时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verify`
--

CREATE TABLE IF NOT EXISTS `verify` (
  `id` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '激活类型：1-自行激活、2-管理员激活',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `token` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '校验token',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `verify_code`
--

CREATE TABLE IF NOT EXISTS `verify_code` (
  `id` int(10) unsigned NOT NULL,
  `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户邮箱',
  `code` char(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '验证码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0-未使用、1-已使用、2-已失效',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`code`);

--
-- Indexes for table `coupon`
--
ALTER TABLE `coupon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_sn_unique` (`sn`);

--
-- Indexes for table `coupon_log`
--
ALTER TABLE `coupon_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_log_coupon_id_foreign` (`coupon_id`),
  ADD KEY `coupon_log_goods_id_foreign` (`goods_id`),
  ADD KEY `coupon_log_order_id_foreign` (`order_id`);

--
-- Indexes for table `email_filter`
--
ALTER TABLE `email_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_filter_words_type_index` (`words`,`type`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invite`
--
ALTER TABLE `invite`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite_code_unique` (`code`),
  ADD KEY `invite_inviter_id_foreign` (`inviter_id`),
  ADD KEY `invite_invitee_id_foreign` (`invitee_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing`
--
ALTER TABLE `marketing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `node_auth`
--
ALTER TABLE `node_auth`
  ADD PRIMARY KEY (`id`),
  ADD KEY `node_auth_node_id_foreign` (`node_id`);

--
-- Indexes for table `node_certificate`
--
ALTER TABLE `node_certificate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `node_daily_data_flow`
--
ALTER TABLE `node_daily_data_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `node_daily_data_flow_node_id_index` (`node_id`);

--
-- Indexes for table `node_hourly_data_flow`
--
ALTER TABLE `node_hourly_data_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `node_hourly_data_flow_node_id_index` (`node_id`);

--
-- Indexes for table `node_label`
--
ALTER TABLE `node_label`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_node_label` (`node_id`,`label_id`),
  ADD KEY `node_label_label_id_foreign` (`label_id`);

--
-- Indexes for table `node_ping`
--
ALTER TABLE `node_ping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `node_ping_node_id_index` (`node_id`);

--
-- Indexes for table `node_rule`
--
ALTER TABLE `node_rule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `node_rule_node_id_rule_id_index` (`node_id`,`rule_id`),
  ADD KEY `node_rule_rule_id_foreign` (`rule_id`);

--
-- Indexes for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_search` (`user_id`,`goods_id`,`is_expire`,`status`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_user_id_order_id_index` (`user_id`,`order_id`);

--
-- Indexes for table `payment_callback`
--
ALTER TABLE `payment_callback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products_pool`
--
ALTER TABLE `products_pool`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_apply`
--
ALTER TABLE `referral_apply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_apply_user_id_foreign` (`user_id`);

--
-- Indexes for table `referral_log`
--
ALTER TABLE `referral_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_log_invitee_id_foreign` (`invitee_id`),
  ADD KEY `referral_log_order_id_foreign` (`order_id`),
  ADD KEY `referral_log_inviter_id_invitee_id_index` (`inviter_id`,`invitee_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `rule`
--
ALTER TABLE `rule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rule_group`
--
ALTER TABLE `rule_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rule_group_node`
--
ALTER TABLE `rule_group_node`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rule_group_node_rule_group_id_foreign` (`rule_group_id`),
  ADD KEY `rule_group_node_node_id_foreign` (`node_id`);

--
-- Indexes for table `rule_log`
--
ALTER TABLE `rule_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx` (`user_id`,`node_id`,`rule_id`),
  ADD KEY `rule_log_node_id_foreign` (`node_id`),
  ADD KEY `rule_log_rule_id_foreign` (`rule_id`);

--
-- Indexes for table `ss_config`
--
ALTER TABLE `ss_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_config_type_index` (`type`);

--
-- Indexes for table `ss_node`
--
ALTER TABLE `ss_node`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_node_is_subscribe_index` (`is_subscribe`),
  ADD KEY `ss_node_type_index` (`type`);

--
-- Indexes for table `ss_node_info`
--
ALTER TABLE `ss_node_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_node_info_node_id_index` (`node_id`);

--
-- Indexes for table `ss_node_ip`
--
ALTER TABLE `ss_node_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_node_ip_node_id_index` (`node_id`),
  ADD KEY `ss_node_ip_user_id_index` (`user_id`),
  ADD KEY `ss_node_ip_port_index` (`port`);

--
-- Indexes for table `ss_node_online_log`
--
ALTER TABLE `ss_node_online_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_node_online_log_node_id_index` (`node_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_user_id_foreign` (`user_id`),
  ADD KEY `ticket_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `ticket_reply`
--
ALTER TABLE `ticket_reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_reply_user_id_foreign` (`user_id`),
  ADD KEY `ticket_reply_admin_id_foreign` (`admin_id`),
  ADD KEY `ticket_reply_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_email_unique` (`email`),
  ADD KEY `idx_search` (`enable`,`status`,`port`),
  ADD KEY `user_inviter_id_foreign` (`inviter_id`);

--
-- Indexes for table `user_baned_log`
--
ALTER TABLE `user_baned_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_baned_log_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_credit_log`
--
ALTER TABLE `user_credit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_credit_log_user_id_foreign` (`user_id`),
  ADD KEY `user_credit_log_order_id_foreign` (`order_id`);

--
-- Indexes for table `user_daily_data_flow`
--
ALTER TABLE `user_daily_data_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_node` (`user_id`,`node_id`),
  ADD KEY `user_daily_data_flow_node_id_foreign` (`node_id`);

--
-- Indexes for table `user_data_modify_log`
--
ALTER TABLE `user_data_modify_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_data_modify_log_user_id_foreign` (`user_id`),
  ADD KEY `user_data_modify_log_order_id_foreign` (`order_id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_hourly_data_flow`
--
ALTER TABLE `user_hourly_data_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_node` (`user_id`,`node_id`),
  ADD KEY `user_hourly_data_flow_node_id_foreign` (`node_id`);

--
-- Indexes for table `user_login_log`
--
ALTER TABLE `user_login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_login_log_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_subscribe`
--
ALTER TABLE `user_subscribe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_subscribe_code_unique` (`code`),
  ADD KEY `user_id` (`user_id`,`status`),
  ADD KEY `user_subscribe_code_index` (`code`);

--
-- Indexes for table `user_subscribe_log`
--
ALTER TABLE `user_subscribe_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_subscribe_log_user_subscribe_id_index` (`user_subscribe_id`);

--
-- Indexes for table `user_traffic_log`
--
ALTER TABLE `user_traffic_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_node_time` (`user_id`,`node_id`,`log_time`),
  ADD KEY `user_traffic_log_node_id_foreign` (`node_id`);

--
-- Indexes for table `verify`
--
ALTER TABLE `verify`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verify_user_id_foreign` (`user_id`);

--
-- Indexes for table `verify_code`
--
ALTER TABLE `verify_code`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `coupon`
--
ALTER TABLE `coupon`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `coupon_log`
--
ALTER TABLE `coupon_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `email_filter`
--
ALTER TABLE `email_filter`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `goods`
--
ALTER TABLE `goods`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `invite`
--
ALTER TABLE `invite`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `label`
--
ALTER TABLE `label`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `level`
--
ALTER TABLE `level`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `marketing`
--
ALTER TABLE `marketing`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_auth`
--
ALTER TABLE `node_auth`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_certificate`
--
ALTER TABLE `node_certificate`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_daily_data_flow`
--
ALTER TABLE `node_daily_data_flow`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_hourly_data_flow`
--
ALTER TABLE `node_hourly_data_flow`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_label`
--
ALTER TABLE `node_label`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_ping`
--
ALTER TABLE `node_ping`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_rule`
--
ALTER TABLE `node_rule`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `notification_log`
--
ALTER TABLE `notification_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payment_callback`
--
ALTER TABLE `payment_callback`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `products_pool`
--
ALTER TABLE `products_pool`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `referral_apply`
--
ALTER TABLE `referral_apply`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `referral_log`
--
ALTER TABLE `referral_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rule`
--
ALTER TABLE `rule`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rule_group`
--
ALTER TABLE `rule_group`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rule_group_node`
--
ALTER TABLE `rule_group_node`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rule_log`
--
ALTER TABLE `rule_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ss_config`
--
ALTER TABLE `ss_config`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ss_node`
--
ALTER TABLE `ss_node`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ss_node_info`
--
ALTER TABLE `ss_node_info`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ss_node_ip`
--
ALTER TABLE `ss_node_ip`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ss_node_online_log`
--
ALTER TABLE `ss_node_online_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ticket_reply`
--
ALTER TABLE `ticket_reply`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_baned_log`
--
ALTER TABLE `user_baned_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_credit_log`
--
ALTER TABLE `user_credit_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_daily_data_flow`
--
ALTER TABLE `user_daily_data_flow`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_data_modify_log`
--
ALTER TABLE `user_data_modify_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_hourly_data_flow`
--
ALTER TABLE `user_hourly_data_flow`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_login_log`
--
ALTER TABLE `user_login_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_subscribe`
--
ALTER TABLE `user_subscribe`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_subscribe_log`
--
ALTER TABLE `user_subscribe_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_traffic_log`
--
ALTER TABLE `user_traffic_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `verify`
--
ALTER TABLE `verify`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `verify_code`
--
ALTER TABLE `verify_code`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- 限制导出的表
--

--
-- 限制表 `coupon_log`
--
ALTER TABLE `coupon_log`
  ADD CONSTRAINT `coupon_log_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_log_goods_id_foreign` FOREIGN KEY (`goods_id`) REFERENCES `goods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE;

--
-- 限制表 `invite`
--
ALTER TABLE `invite`
  ADD CONSTRAINT `invite_invitee_id_foreign` FOREIGN KEY (`invitee_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invite_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- 限制表 `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_auth`
--
ALTER TABLE `node_auth`
  ADD CONSTRAINT `node_auth_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_daily_data_flow`
--
ALTER TABLE `node_daily_data_flow`
  ADD CONSTRAINT `node_daily_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_hourly_data_flow`
--
ALTER TABLE `node_hourly_data_flow`
  ADD CONSTRAINT `node_hourly_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_label`
--
ALTER TABLE `node_label`
  ADD CONSTRAINT `node_label_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `node_label_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_ping`
--
ALTER TABLE `node_ping`
  ADD CONSTRAINT `node_ping_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `node_rule`
--
ALTER TABLE `node_rule`
  ADD CONSTRAINT `node_rule_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `node_rule_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE;

--
-- 限制表 `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `referral_apply`
--
ALTER TABLE `referral_apply`
  ADD CONSTRAINT `referral_apply_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `referral_log`
--
ALTER TABLE `referral_log`
  ADD CONSTRAINT `referral_log_invitee_id_foreign` FOREIGN KEY (`invitee_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_log_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL;

--
-- 限制表 `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- 限制表 `rule_group_node`
--
ALTER TABLE `rule_group_node`
  ADD CONSTRAINT `rule_group_node_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rule_group_node_rule_group_id_foreign` FOREIGN KEY (`rule_group_id`) REFERENCES `rule_group` (`id`) ON DELETE CASCADE;

--
-- 限制表 `rule_log`
--
ALTER TABLE `rule_log`
  ADD CONSTRAINT `rule_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rule_log_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rule_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `ss_node_info`
--
ALTER TABLE `ss_node_info`
  ADD CONSTRAINT `ss_node_info_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `ss_node_ip`
--
ALTER TABLE `ss_node_ip`
  ADD CONSTRAINT `ss_node_ip_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ss_node_ip_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `ss_node_online_log`
--
ALTER TABLE `ss_node_online_log`
  ADD CONSTRAINT `ss_node_online_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE;

--
-- 限制表 `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `ticket_reply`
--
ALTER TABLE `ticket_reply`
  ADD CONSTRAINT `ticket_reply_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_reply_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_reply_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- 限制表 `user_baned_log`
--
ALTER TABLE `user_baned_log`
  ADD CONSTRAINT `user_baned_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_credit_log`
--
ALTER TABLE `user_credit_log`
  ADD CONSTRAINT `user_credit_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_credit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_daily_data_flow`
--
ALTER TABLE `user_daily_data_flow`
  ADD CONSTRAINT `user_daily_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_daily_data_flow_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_data_modify_log`
--
ALTER TABLE `user_data_modify_log`
  ADD CONSTRAINT `user_data_modify_log_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_data_modify_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_hourly_data_flow`
--
ALTER TABLE `user_hourly_data_flow`
  ADD CONSTRAINT `user_hourly_data_flow_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_hourly_data_flow_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_login_log`
--
ALTER TABLE `user_login_log`
  ADD CONSTRAINT `user_login_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_subscribe`
--
ALTER TABLE `user_subscribe`
  ADD CONSTRAINT `user_subscribe_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_subscribe_log`
--
ALTER TABLE `user_subscribe_log`
  ADD CONSTRAINT `user_subscribe_log_user_subscribe_id_foreign` FOREIGN KEY (`user_subscribe_id`) REFERENCES `user_subscribe` (`id`) ON DELETE CASCADE;

--
-- 限制表 `user_traffic_log`
--
ALTER TABLE `user_traffic_log`
  ADD CONSTRAINT `user_traffic_log_node_id_foreign` FOREIGN KEY (`node_id`) REFERENCES `ss_node` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_traffic_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 限制表 `verify`
--
ALTER TABLE `verify`
  ADD CONSTRAINT `verify_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
