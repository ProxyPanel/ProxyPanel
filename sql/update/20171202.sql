-- 加入流量重置日
ALTER TABLE `user`
ADD COLUMN `traffic_reset_day`  tinyint NOT NULL DEFAULT 0 COMMENT '流量自动重置日，0表示不重置' AFTER `referral_uid`;

-- 加入 是否自动重置流量 配置
INSERT INTO `config` VALUES ('36', 'is_clear_log', 1);