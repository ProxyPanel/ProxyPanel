-- 文章加入简介
ALTER TABLE `article`
  ADD COLUMN `summary` varchar(255) DEFAULT '' COMMENT '简介' AFTER `author`;