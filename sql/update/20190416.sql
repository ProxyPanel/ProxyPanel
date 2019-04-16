-- 移除商品积分字段
ALTER TABLE `goods`
	DROP COLUMN `score`;

-- 移除用户积分字段
ALTER TABLE `user`
	DROP COLUMN `score`;

-- 移除用户积分日志表
DROP TABLE `user_score_log`;