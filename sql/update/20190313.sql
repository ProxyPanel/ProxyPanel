-- 节点适配rico93版的v2ray插件
ALTER TABLE ss_node
  ADD COLUMN v2_insider_port INT NOT NULL DEFAULT '10550' COMMENT 'V2ray内部端口（内部监听），v2_port为0时有效' AFTER v2_tls,
  ADD COLUMN v2_outsider_port INT NOT NULL DEFAULT '443' COMMENT 'V2ray外部端口（外部覆盖），v2_port为0时有效' AFTER v2_insider_port;