-- 修正节点不填写域名错误
alter table ss_node modify column server varchar(255) null default '';