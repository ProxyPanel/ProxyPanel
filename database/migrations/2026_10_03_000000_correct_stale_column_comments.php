<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 列注释补齐到代码实际判据：node.type 漏了 0=Shadowsocks，order.status 漏了 3=预支付排队，
 * order.pay_type 漏了 6=stripe、7=人工收款。
 */
return new class extends Migration
{
    private const NODE_TYPE = "ALTER TABLE `node` MODIFY `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '服务类型：0-Shadowsocks、1-ShadowsocksR、2-V2Ray、3-Trojan、4-VNET(由SSR承载)、5-Hysteria2'";

    private const NODE_TYPE_OLD = "ALTER TABLE `node` MODIFY `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '服务类型：1-Shadowsocks(R)、2-V2ray、3-Trojan、4-VNet、5-Hysteria2'";

    private const ORDER_STATUS = "ALTER TABLE `order` MODIFY `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成、3-预支付排队'";

    private const ORDER_STATUS_OLD = "ALTER TABLE `order` MODIFY `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态：-1-已关闭、0-待支付、1-已支付待确认、2-已完成'";

    private const ORDER_PAY_TYPE = "ALTER TABLE `order` MODIFY `pay_type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal、6-stripe、7-人工收款'";

    private const ORDER_PAY_TYPE_OLD = "ALTER TABLE `order` MODIFY `pay_type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '支付渠道：0-余额、1-支付宝、2-QQ、3-微信、4-虚拟货币、5-paypal'";

    public function up(): void
    {
        DB::statement(self::NODE_TYPE);
        DB::statement(self::ORDER_STATUS);
        DB::statement(self::ORDER_PAY_TYPE);
    }

    public function down(): void
    {
        DB::statement(self::NODE_TYPE_OLD);
        DB::statement(self::ORDER_STATUS_OLD);
        DB::statement(self::ORDER_PAY_TYPE_OLD);
    }
};
