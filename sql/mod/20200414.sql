-- 返利模式可调：1-初次返利、2-循环返利
UPDATE `config` SET `name` = 'referral_type', `value` = '0' where `config`.`id` = 10;
-- 增加：hCaptcha的密钥和网站密钥
INSERT INTO `config` VALUES ('105', 'hcaptcha_secret', '');
INSERT INTO `config` VALUES ('106', 'hcaptcha_sitekey', '');