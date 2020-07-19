-- 触发审计规则后强制跳转地址
INSERT INTO `config` VALUES ('112', 'redirect_url', '');

-- 移除审计规则无用字段
ALTER TABLE `rule`
    DROP COLUMN `created_at`,
    DROP COLUMN `updated_at`;

-- 加入新审计规则条目：外汇交易类
INSERT INTO `rule` (`id`, `type`, `name`, `pattern`)
VALUES (18, '1', '外汇交易类', '(.*\.||)(metatrader4|metatrader5|mql5)\.(org|com|net)');
