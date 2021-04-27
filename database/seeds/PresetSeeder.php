<?php

use App\Models\Config;
use App\Models\Country;
use App\Models\EmailFilter;
use App\Models\Label;
use App\Models\Level;
use App\Models\Rule;
use App\Models\SsConfig;
use Illuminate\Database\Seeder;

class PresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 系统参数
        $configList = [
            'is_rand_port',
            'is_user_rand_port',
            'invite_num',
            'is_register',
            'is_invite_register',
            'website_name',
            'is_reset_password',
            'reset_password_times',
            'website_url',
            'referral_type',
            'active_times',
            'is_checkin',
            'min_rand_traffic',
            'max_rand_traffic',
            'wechat_qrcode',
            'alipay_qrcode',
            'traffic_limit_time',
            'referral_traffic',
            'referral_percent',
            'referral_money',
            'referral_status',
            'default_traffic',
            'traffic_warning',
            'traffic_warning_percent',
            'expire_warning',
            'expire_days',
            'reset_traffic',
            'default_days',
            'subscribe_max',
            'min_port',
            'max_port',
            'is_captcha',
            'is_traffic_ban',
            'traffic_ban_value',
            'traffic_ban_time',
            'is_clear_log',
            'is_node_offline',
            'webmaster_email',
            'is_notification',
            'server_chan_key',
            'is_subscribe_ban',
            'subscribe_ban_times',
            'codepay_url',
            'codepay_id',
            'codepay_key',
            'is_free_code',
            'is_forbid_robot',
            'subscribe_domain',
            'auto_release_port',
            'website_callback_url',
            'web_api_url',
            'v2ray_license',
            'trojan_license',
            'v2ray_tls_provider',
            'website_analytics',
            'website_customer_service',
            'register_ip_limit',
            'is_email_filtering',
            'is_push_bear',
            'push_bear_send_key',
            'push_bear_qrcode',
            'is_ban_status',
            'is_namesilo',
            'namesilo_key',
            'website_logo',
            'website_home_logo',
            'nodes_detection',
            'detection_check_times',
            'is_forbid_china',
            'is_forbid_oversea',
            'AppStore_id',
            'AppStore_password',
            'is_activate_account',
            'node_daily_report',
            'rand_subscribe',
            'is_custom_subscribe',
            'is_AliPay',
            'is_QQPay',
            'is_WeChatPay',
            'is_otherPay',
            'alipay_private_key',
            'alipay_public_key',
            'alipay_transport',
            'alipay_currency',
            'bitpay_secret',
            'f2fpay_app_id',
            'f2fpay_private_key',
            'f2fpay_public_key',
            'website_security_code',
            'subject_name',
            'user_invite_days',
            'admin_invite_days',
            'offline_check_times',
            'payjs_mch_id',
            'payjs_key',
            'maintenance_mode',
            'maintenance_time',
            'maintenance_content',
            'bark_key',
            'paypal_username',
            'paypal_password',
            'paypal_secret',
            'paypal_certificate',
            'paypal_app_id',
            'redirect_url',
            'epay_url',
            'epay_mch_id',
            'epay_key',
        ];

        foreach ($configList as $config) {
            Config::insert(['name' => $config]);
        }

        $presetDates = [
            'invite_num' => 3,
            'is_register' => 1,
            'is_invite_register' => 2,
            'website_name' => 'ProxyPanel',
            'is_reset_password' => 1,
            'reset_password_times' => 3,
            'website_url' => 'https://demo.proxypanel.ml',
            'active_times' => 3,
            'is_checkin' => 1,
            'min_rand_traffic' => 10,
            'max_rand_traffic' => 500,
            'traffic_limit_time' => 1440,
            'referral_traffic' => 1024,
            'referral_percent' => 0.2,
            'referral_money' => 100,
            'referral_status' => 1,
            'default_traffic' => 1024,
            'traffic_warning_percent' => 80,
            'expire_days' => 15,
            'reset_traffic' => 1,
            'default_days' => 7,
            'subscribe_max' => 3,
            'min_port' => 10000,
            'max_port' => 65535,
            'is_traffic_ban' => 1,
            'traffic_ban_value' => 10,
            'traffic_ban_time' => 60,
            'is_clear_log' => 1,
            'is_subscribe_ban' => 1,
            'subscribe_ban_times' => 20,
            'auto_release_port' => 1,
            'register_ip_limit' => 5,
            'detection_check_times' => 3,
            'alipay_transport' => 'http',
            'alipay_currency' => 'USD',
            'user_invite_days' => 7,
            'admin_invite_days' => 7,
        ];

        foreach ($presetDates as $key => $value) {
            Config::whereName($key)->update(['value' => $value]);
        }

        // 生成最初的等级
        Level::insert(['level' => 0, 'name' => 'Free']);
        for ($i = 1; $i < 8; $i++) {
            Level::insert(['level' => $i, 'name' => 'VIP-'.$i]);
        }

        // ss系列 加密方式
        SsConfig::insert(['name' => 'none', 'type' => 1, 'is_default' => 1]);
        SsConfig::insert(['name' => 'rc4-md5']);
        SsConfig::insert(['name' => 'aes-128-cfb']);
        SsConfig::insert(['name' => 'aes-192-cfb']);
        SsConfig::insert(['name' => 'aes-256-cfb']);
        SsConfig::insert(['name' => 'aes-128-ctr']);
        SsConfig::insert(['name' => 'aes-192-ctr']);
        SsConfig::insert(['name' => 'aes-256-ctr']);
        SsConfig::insert(['name' => 'aes-128-gcm']);
        SsConfig::insert(['name' => 'aes-192-gcm']);
        SsConfig::insert(['name' => 'aes-256-gcm']);
        SsConfig::insert(['name' => 'bf-cfb']);
        SsConfig::insert(['name' => 'cast5-cfb']);
        SsConfig::insert(['name' => 'des-cfb']);
        SsConfig::insert(['name' => 'salsa20']);
        SsConfig::insert(['name' => 'chacha20']);
        SsConfig::insert(['name' => 'chacha20-ietf']);
        SsConfig::insert(['name' => 'chacha20-ietf-poly1305']);

        // ss系列 协议
        SsConfig::insert(['name' => 'origin', 'type' => 2, 'is_default' => 1]);
        SsConfig::insert(['name' => 'auth_sha1_v4', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_aes128_md5', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_aes128_sha1', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_a', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_b', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_c', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_d', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_e', 'type' => 2]);
        SsConfig::insert(['name' => 'auth_chain_f', 'type' => 2]);

        // ss系列 混淆
        SsConfig::insert(['name' => 'plain', 'type' => 3, 'is_default' => 1]);
        SsConfig::insert(['name' => 'http_simple', 'type' => 3]);
        SsConfig::insert(['name' => 'http_post', 'type' => 3]);
        SsConfig::insert(['name' => 'tls1.2_ticket_auth', 'type' => 3]);
        SsConfig::insert(['name' => 'tls1.2_ticket_fastauth', 'type' => 3]);

        // 节点用标签
        $labelList = [
            'Netflix',
            'Hulu',
            'HBO',
            'Amazon Video',
            'DisneyNow',
            'BBC',
            'Channel 4',
            'Fox+',
            'Happyon',
            'AbemeTV',
            'DMM',
            'NicoNico',
            'Pixiv',
            'TVer',
            'TVB',
            'HBO Go',
            'BiliBili港澳台',
            '動畫瘋',
            '四季線上影視',
            'LINE TV',
            'Youtube Premium',
            '中国视频网站',
            '网易云音乐',
            'QQ音乐',
            'DisneyPlus',
            'Pandora',
            'SoundCloud',
            'Spotify',
            'TIDAL',
            'TikTok',
            'Pornhub',
            'Twitch',
        ];

        foreach ($labelList as $label) {
            Label::insert(['name' => $label]);
        }

        // 黑名单邮箱 过滤列表
        $blackEmailSuffixList = [
            'chacuo.com',
            '1766258.com',
            '3202.com',
            '4057.com',
            '4059.com',
            'a7996.com',
            'bccto.me',
            'bnuis.com',
            'chaichuang.com',
            'cr219.com',
            'cuirushi.org',
            'dawin.com',
            'jiaxin8736.com',
            'lakqs.com',
            'urltc.com',
            '027168.com',
            '10minutemail.net',
            '11163.com',
            '1shivom.com',
            'auoie.com',
            'bareed.ws',
            'bit-degree.com',
            'cjpeg.com',
            'cool.fr.nf',
            'courriel.fr.nf',
            'disbox.net',
            'disbox.org',
            'fidelium10.com',
            'get365.pw',
            'ggr.la',
            'grr.la',
            'guerrillamail.biz',
            'guerrillamail.com',
            'guerrillamail.de',
            'guerrillamail.net',
            'guerrillamail.org',
            'guerrillamailblock.com',
            'hubii-network.com',
            'hurify1.com',
            'itoup.com',
            'jetable.fr.nf',
            'jnpayy.com',
            'juyouxi.com',
            'mail.bccto.me',
            'www.bccto.me',
            'mega.zik.dj',
            'moakt.co',
            'moakt.ws',
            'molms.com',
            'moncourrier.fr.nf',
            'monemail.fr.nf',
            'monmail.fr.nf',
            'nomail.xl.cx',
            'nospam.ze.tc',
            'pay-mon.com',
            'poly-swarm.com',
            'sgmh.online',
            'sharklasers.com',
            'shiftrpg.com',
            'spam4.me',
            'speed.1s.fr',
            'tmail.ws',
            'tmails.net',
            'tmpmail.net',
            'tmpmail.org',
            'travala10.com',
            'yopmail.com',
            'yopmail.fr',
            'yopmail.net',
            'yuoia.com',
            'zep-hyr.com',
            'zippiex.com',
            'lrc8.com',
            '1otc.com',
            'emailna.co',
            'mailinator.com',
            'nbzmr.com',
            'awsoo.com',
            'zhcne.com',
            '0box.eu',
            'contbay.com',
            'damnthespam.com',
            'kurzepost.de',
            'objectmail.com',
            'proxymail.eu',
            'rcpt.at',
            'trash-mail.at',
            'trashmail.at',
            'trashmail.com',
            'trashmail.io',
            'trashmail.me',
            'trashmail.net',
            'wegwerfmail.de',
            'wegwerfmail.net',
            'wegwerfmail.org',
            'nwytg.net',
            'despam.it',
            'spambox.us',
            'spam.la',
            'mytrashmail.com',
            'mt2014.com',
            'mt2015.com',
            'thankyou2010.com',
            'trash2009.com',
            'mt2009.com',
            'trashymail.com',
            'tempemail.net',
            'slopsbox.com',
            'mailnesia.com',
            'ezehe.com',
            'tempail.com',
            'newairmail.com',
            'temp-mail.org',
            'linshiyouxiang.net',
            'zwoho.com',
            'mailboxy.fun',
            'crypto-net.club',
            'guerrillamail.info',
            'pokemail.net',
            'odmail.cn',
            'hlooy.com',
            'ozlaq.com',
            '666email.com',
            'linshiyou.com',
            'linshiyou.pl',
            'woyao.pl',
            'yaowo.pl',
        ];

        foreach ($blackEmailSuffixList as $emailSuffix) {
            EmailFilter::insert(['type' => 1, 'words' => $emailSuffix]);
        }

        // 白名单邮箱 过滤列表
        $whiteEmailSuffixList = [
            'qq.com',
            '163.com',
            '126.com',
            '189.com',
            'sohu.com',
            'gmail.com',
            'outlook.com',
            'icloud.com',
        ];

        foreach ($whiteEmailSuffixList as $emailSuffix) {
            EmailFilter::insert(['type' => 2, 'words' => $emailSuffix]);
        }

        $countryList = [
            'au' => '澳大利亚',
            'br' => '巴西',
            'ca' => '加拿大',
            'ch' => '瑞士',
            'cn' => '中国',
            'de' => '德国',
            'dk' => '丹麦',
            'eg' => '埃及',
            'fr' => '法国',
            'gr' => '希腊',
            'hk' => '香港',
            'id' => '印度尼西亚',
            'ie' => '爱尔兰',
            'il' => '以色列',
            'in' => '印度',
            'iq' => '伊拉克',
            'ir' => '伊朗',
            'it' => '意大利',
            'jp' => '日本',
            'kr' => '韩国',
            'mx' => '墨西哥',
            'my' => '马来西亚',
            'nl' => '荷兰',
            'no' => '挪威',
            'nz' => '纽西兰',
            'ph' => '菲律宾',
            'ru' => '俄罗斯',
            'se' => '瑞典',
            'sg' => '新加坡',
            'th' => '泰国',
            'tr' => '土耳其',
            'tw' => '台湾',
            'uk' => '英国',
            'us' => '美国',
            'vn' => '越南',
            'pl' => '波兰',
            'kz' => '哈萨克斯坦',
            'ua' => '乌克兰',
            'ro' => '罗马尼亚',
            'ae' => '阿联酋',
            'za' => '南非',
            'mm' => '缅甸',
            'is' => '冰岛',
            'fi' => '芬兰',
            'lu' => '卢森堡',
            'be' => '比利时',
            'bg' => '保加利亚',
            'lt' => '立陶宛',
            'co' => '哥伦比亚',
            'mo' => '澳门',
            'ke' => '肯尼亚',
            'cz' => '捷克',
            'md' => '摩尔多瓦',
            'es' => '西班牙',
            'pk' => '巴基斯坦',
            'pt' => '葡萄牙',
            'hu' => '匈牙利',
            'ar' => '阿根廷',
        ];

        foreach ($countryList as $code => $name) {
            Country::insert(['code' => $code, 'name' => $name]);
        }

        // 审核规则
        $ruleList = [
            '360' => '(.*.||)(^360|0360|1360|3600|360safe|^so|qhimg|qhmsg|^yunpan|qihoo|qhcdn|qhupdate|360totalsecurity|360shouji|qihucdn|360kan|secmp).(cn|com|net)',
            '腾讯管家' => '(.guanjia.qq.com|qqpcmgr|QQPCMGR)',
            '金山毒霸' => '(.*.||)(rising|kingsoft|duba|xindubawukong|jinshanduba).(com|net|org)',
            '暗网相关' => '(.*.||)(netvigator|torproject).(cn|com|net|org)',
            '百度定位' => '(api|ps|sv|offnavi|newvector|ulog.imap|newloc|tracknavi)(.map|).(baidu|n.shifen).com',
            '法轮功类' => '(.*.||)(dafahao|minghui|dongtaiwang|dajiyuan|falundata|shenyun|tuidang|epochweekly|epochtimes|ntdtv|falundafa|wujieliulan|zhengjian).(org|com|net)',
            'BT扩展名' => '(torrent|.torrent|peer_id=|info_hash|get_peers|find_node|BitTorrent|announce_peer|announce.php?passkey=)',
            '邮件滥发' => '((^.*@)(guerrillamail|guerrillamailblock|sharklasers|grr|pokemail|spam4|bccto|chacuo|027168).(info|biz|com|de|net|org|me|la)|Subject|HELO|SMTP)',
            '迅雷下载' => '(.?)(xunlei|sandai|Thunder|XLLiveUD)(.)',
            '大陆应用' => '(.*.||)(baidu|qq|163|189|10000|10010|10086|sohu|sogoucdn|sogou|uc|58|taobao|qpic|bilibili|hdslb|acgvideo|sina|douban|doubanio|xiaohongshu|sinaimg|weibo|xiaomi|youzanyun|meituan|dianping|biliapi|huawei|pinduoduo|cnzz).(org|com|net|cn)',
            '大陆银行' => '(.*.||)(icbc|ccb|boc|bankcomm|abchina|cmbchina|psbc|cebbank|cmbc|pingan|spdb|citicbank|cib|hxb|bankofbeijing|hsbank|tccb|4001961200|bosc|hkbchina|njcb|nbcb|lj-bank|bjrcb|jsbchina|gzcb|cqcbank|czbank|hzbank|srcb|cbhb|cqrcb|grcbank|qdccb|bocd|hrbcb|jlbank|bankofdl|qlbchina|dongguanbank|cscb|hebbank|drcbank|zzbank|bsb|xmccb|hljrcc|jxnxs|gsrcu|fjnx|sxnxs|gx966888|gx966888|zj96596|hnnxs|ahrcu|shanxinj|hainanbank|scrcu|gdrcu|hbxh|ynrcc|lnrcc|nmgnxs|hebnx|jlnls|js96008|hnnx|sdnxs).(org|com|net|cn)',
            '台湾银行' => '(.*.||)(firstbank|bot|cotabank|megabank|tcb-bank|landbank|hncb|bankchb|tbb|ktb|tcbbank|scsb|bop|sunnybank|kgibank|fubon|ctbcbank|cathaybk|eximbank|bok|ubot|feib|yuantabank|sinopac|esunbank|taishinbank|jihsunbank|entiebank|hwataibank|csc|skbank).(org|com|net|tw)',
            '大陆第三方支付' => '(.*.||)(alipay|baifubao|yeepay|99bill|95516|51credit|cmpay|tenpay|lakala|jdpay).(org|com|net|cn)',
            '台湾特供' => '(.*.||)(visa|mycard|mastercard|gov|gash|beanfun|bank|line).(org|com|net|cn|tw|jp|kr)',
            '涉政治类' => '(.*.||)(shenzhoufilm|secretchina|renminbao|aboluowang|mhradio|guangming|zhengwunet|soundofhope|yuanming|zhuichaguoji|fgmtv|xinsheng|shenyunperformingarts|epochweekly|tuidang|shenyun|falundata|bannedbook|pincong|rfi|mingjingnews|boxun|rfa|scmp|ogate|voachinese).(org|com|net|rocks|fr)',
            '流媒体' => '(.*.||)(youtube|googlevideo|hulu|netflix|nflxvideo|akamai|nflximg|hbo|mtv|bbc|tvb).(org|club|com|net|tv)',
            '测速类' => '(.*.||)(fast|speedtest).(org|com|net|cn)',
            '外汇交易类' => '(.*.||)(metatrader4|metatrader5|mql5).(org|com|net)',
        ];

        foreach ($ruleList as $name => $pattern) {
            Rule::insert(['type' => 1, 'name' => $name, 'pattern' => $pattern]);
        }

        // 生成初始管理账号
        $user = Helpers::addUser('test@test.com', '123456', 100 * GB, sysConfig('default_days'), null, '管理员');
        $user->assignRole('Super Admin');
    }
}
