-- 加入流量重置日
ALTER TABLE `user`
ADD COLUMN `traffic_reset_day`  tinyint NOT NULL DEFAULT 0 COMMENT '流量自动重置日，0表示不重置' AFTER `referral_uid`;

