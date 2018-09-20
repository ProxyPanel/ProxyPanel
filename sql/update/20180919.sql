-- 将invite表 uid=1 的改为 0（系统生成）
UPDATE `invite` SET `uid` = 0 WHERE `uid` = 1;