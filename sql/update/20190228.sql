-- 登录送积分功能改为手动签到送流量功能
UPDATE `config` SET `name` = 'is_checkin' WHERE id=12;
UPDATE `config` SET `name` = 'min_rand_traffic' WHERE id=13;
UPDATE `config` SET `name` = 'max_rand_traffic' WHERE id=14;
UPDATE `config` SET `name` = 'traffic_limit_time' WHERE id=17;

-- 重新定义用户等级表
TRUNCATE TABLE `level`;

INSERT INTO `level` VALUES (1, '1', '普通用户');
INSERT INTO `level` VALUES (2, '2', 'VIP1');
INSERT INTO `level` VALUES (3, '3', 'VIP2');
INSERT INTO `level` VALUES (4, '4', 'VIP3');

