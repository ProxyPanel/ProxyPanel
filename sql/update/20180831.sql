-- 将已购买套餐的用户的流量重置日更改为1
UPDATE `user` SET `traffic_reset_day` = 1 WHERE `traffic_reset_day` > 0;