-- payment表加入qr_local_url字段
ALTER TABLE `payment` ADD COLUMN `qr_local_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付二维码的本地存储URL' AFTER `qr_code`;