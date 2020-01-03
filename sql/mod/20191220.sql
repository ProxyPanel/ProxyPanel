-- 新用户重置日期记录
ALTER TABLE `user` ADD COLUMN `reset_time` DATE DEFAULT NULL COMMENT '流量重置日期，NULL表示不重置' AFTER `traffic_reset_day`;

-- 商品添加流量重置周期
ALTER TABLE `goods`
    ADD COLUMN `period` int(11) NOT NULL DEFAULT '30' COMMENT '流量自动重置周期' AFTER `renew`;


-- 运行下面代码前，先在ssh里运行 php artisan upgradeUserResetTime

-- 删除老日期
ALTER TABLE `user` drop `traffic_reset_day`;
