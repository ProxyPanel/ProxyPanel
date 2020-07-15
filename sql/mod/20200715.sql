ALTER TABLE `order`
    ADD `pay_type` TINYINT(1) UNSIGNED DEFAULT '0' COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal' AFTER `is_expire`;