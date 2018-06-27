INSERT INTO `config` VALUES ('59', 'is_push_bear', 0);
INSERT INTO `config` VALUES ('60', 'push_bear_send_key', '');
INSERT INTO `config` VALUES ('61', 'push_bear_qrcode', '');
INSERT INTO `config` VALUES ('62', 'is_ban_status', 0);


CREATE TABLE `marketing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` TINYINT(4) NOT NULL COMMENT '类型：1-邮件群发、2-订阅渠道群发',
  `receiver` TEXT NOT NULL COMMENT '接收者' COLLATE 'utf8mb4_unicode_ci',
  `title` VARCHAR(255) NOT NULL COMMENT '标题' COLLATE 'utf8mb4_unicode_ci',
  `content` TEXT NOT NULL COMMENT '内容' COLLATE 'utf8mb4_unicode_ci',
  `error` VARCHAR(255) NULL COMMENT '错误信息' COLLATE 'utf8mb4_unicode_ci',
  `status` TINYINT(4) NOT NULL COMMENT '状态：-1-失败、0-待发送、1-成功',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE='utf8mb4_unicode_ci';
