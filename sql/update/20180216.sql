-- 节点分离域名地址和IP地址
alter table ss_node add column `ip` varchar(30) DEFAULT NULL COMMENT '服务器IP地址' after `server`;