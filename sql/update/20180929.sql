-- 必须邀请注册 重新定义为 新值 2
UPDATE `config` SET `is_invite_register` = 2 WHERE `is_invite_register` = 1;