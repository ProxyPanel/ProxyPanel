-- 不管是否更新数据库，先运行 php artisan updateTextToJson 来转换数据库数据为最新适配的版本
-- run php artisan updateTextToJson to reformat database even if you aren't going to update the following sql

-- 可选性更新，推荐数据库版本5.7及以上更新
-- Optional Update, recommend for Mysql version >=5.7

ALTER TABLE `referral_apply`
    CHANGE `link_logs` `link_logs` JSON NOT NULL COMMENT '关联返利日志ID，例如：1,3,4';

ALTER TABLE `user_group`
    CHANGE `nodes` `nodes` JSON DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔';

ALTER TABLE `rule_group`
    CHANGE `rules` `rules` JSON DEFAULT NULL COMMENT '关联的规则ID，多个用,号分隔',
    CHANGE `nodes` `nodes` JSON DEFAULT NULL COMMENT '关联的节点ID，多个用,号分隔';
