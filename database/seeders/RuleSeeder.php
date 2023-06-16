<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    private array $rules = [
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

    public function run(): void
    { // 审核规则
        foreach ($this->rules as $name => $pattern) {
            Rule::insert(['type' => 1, 'name' => $name, 'pattern' => $pattern]);
        }
    }
}
