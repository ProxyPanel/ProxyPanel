-- 流量重置价格，0为关闭功能
ALTER TABLE `goods`
    ADD COLUMN `renew` int(11) NOT NULL DEFAULT '0' COMMENT '流量重置价格，单位分' AFTER `price`;