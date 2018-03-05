-- 去掉user表的唯一索引，解决无法自动释放端口的问题
ALTER TABLE `user` DROP INDEX `port`;