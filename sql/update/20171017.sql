ALTER TABLE `user`
ADD COLUMN `gender`  tinyint NOT NULL DEFAULT 1 COMMENT '性别：0-女、1-男' AFTER `speed_limit_per_user`;

