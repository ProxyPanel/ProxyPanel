-- ss_node 加索引
ALTER TABLE `ss_node`
	ADD INDEX `idx_group` (`group_id`),
	ADD INDEX `idx_sub` (`is_subscribe`);


-- user 加索引
ALTER TABLE `user`
	ADD INDEX `idx_search` (`enable`, `status`);


-- order 加索引
ALTER TABLE `order`
	ADD INDEX `idx_order_search` (`user_id`, `goods_id`, `is_expire`, `status`);
