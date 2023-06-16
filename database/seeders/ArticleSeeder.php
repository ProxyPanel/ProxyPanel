<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**     * Run the database seeds.     */
    public function run(): void
    {
        $articles = [
            '账号&服务' => [
                255 => [
                    '不运行软件，就连不上网，怎么办？',
                    '<h4>方法一</h4><p>电脑有安装任何电脑管家类的软件，都可以使用他们自带的网络修复工具来重置网络。</p><h4>方法二</h4><ol><li>键盘操作<code>Win</code> + <code>X</code>，或右击左下角开始菜单键 （Win键看起来像 <i class="fa-brands fa-windows" aria-hidden="true"></i> 这样）</li><li>按下 <code>A</code>键 或者 手动选择 <code>命令提示符（管理员）/ Windows PowerShell(管理员)</code></li><li>输入<code>Netsh winsock reset</code> 后回车，再输入 <code>netsh advfirewall reset</code> 后回车；</li></ol>',
                ], 254 => [
                    '为什么我的账号状态显示是禁用?',
                    '<p>账号在2种情况下会显示禁用；</p><ol><li>套餐过期/流量枯竭；此情况您需要重新购买/重置 <a href="services">【服 务】</a>；</li><li>近期流量使用异常；在<code>1小时</code>内使用流量超过 <code>10GB</code> ，即会触发本站的流量异常保护；保护时长为 <code>60分钟</code></li></ol><p>如您对禁用情况有疑问，可以创建<a href="tickets">【工 单】</a>，联系售后人员。</p>',
                ], 253 => [
                    '为什么我的订阅链接被禁用了？',
                    '<p>订阅地址对于账号来说非常重要。所以本站对此设置了严格的限制措施，以防止用户无意间泄露给他人后，无法挽回。</p><p>限制为： <code>24小时</code>内，订阅地址只允许请求 <code>20次</code></p><p>解封，请在过一段时间并确定无误后，创建<a href="tickets">【工 单】</a>，联系售后人员</p><p>小知识：如果您无意间的截图忘记将订阅地址打码了，您可以 点击上方 更换按钮</p>',
                ], 252 => [
                    '我想续费/购买服务，该怎么操作？',
                    '<ol><li>在线支付，本支付方式支持支付宝。支付后即开即用。前往 <a href="services">【服 务】</a> 选择想要购买的套餐，在订单界面选择<code>在线支付</code>即可。</li><li>余额支付，本支付方法支持微信，支付宝。支付后需要等待充值到账，再购买服务。 ，充值后等待充值到账，一般会在<code>24小时</code>内到账，到账后可以在 <a href="services">【服 务】</a>页面查看您的账号余额。 在<a href="services">【服 务】</a> 选择想要购买的套餐，在订单界面选择<code>余额支付</code>即可。</li></ol>',
                ], 251 => [
                    '怎么样才能快速的联系上客服？',
                    '<blockquote class="blockquote custom-blockquote blockquote-warning">请选择其一种方式联系客服，请勿重复发送请求!!!</blockquote><ol><li>在<a href="tickets">【工 单】</a>界面，创建新的工单，客服人员在上线后会在第一时刻处理。</li></ol>',
                ],
            ], '下载&教程' => [
                99 => [
                    'Windows',
                    '<ol><li><a href="clients/ShadowsocksR-win.zip" target="_blank" rel="noopener">点击此处</a>下载客户端并启动</li><li>运行 ShadowsocksR 文件夹内的 ShadowsocksR.exe</li><li>右击桌面右下角状态栏（或系统托盘）纸飞机 -&gt; 服务器订阅 -&gt; SSR服务器订阅设置</li><li>点击窗口左下角 &ldquo;Add&rdquo; 新增订阅，完整复制本页上方 &ldquo;订阅服务&rdquo; 处地址，将其粘贴至&ldquo;网址&rdquo;栏，点击&ldquo;确定&rdquo;</li><li>右击纸飞机 -&gt; 服务器订阅 -&gt; 更新SSR服务器订阅（不通过代理）</li><li>右击纸飞机 -&gt; 服务器，选定合适服务器</li><li>右击纸飞机 -&gt; 系统代理模式 -&gt; PAC模式</li><li>右击纸飞机 -&gt; PAC -&gt; 更新PAC为GFWList</li><li>右击纸飞机 -&gt; 代理规则 -&gt; 绕过局域网和大陆</li><li>右击纸飞机，取消勾选&ldquo;服务器负载均衡&rdquo;</li></ol>',
                ], 98 => [
                    '安卓',
                    '<ol><li><a href="https://github.com/shadowsocksrr/shadowsocksr-android/releases/download/3.5.3/shadowsocksr-android-3.5.3.apk" target="_blank" rel="noopener">点击此处</a>下载客户端并启动</li><li>单击左上角的shadowsocksR进入配置文件页，点击右下角的&ldquo;+&rdquo;号，点击&ldquo;添加/升级SSR订阅&rdquo;，完整复制本页上方&ldquo;订阅服务&rdquo;处地址，填入订阅信息并保存</li><li>选中任意一个节点，返回软件首页</li><li>在软件首页处找到&ldquo;路由&rdquo;选项，并将其改为&ldquo;绕过局域网及中国大陆地址&rdquo;</li><li>点击右上角的小飞机图标进行连接，提示是否添加（或创建）VPN连接，点同意（或允许）</li></ol>',
                ], 97 => [
                    'iOS',
                    '<ol><li>请从站长处获取 App Store 账号密码</li><li>打开 Shadowrocket，点击右上角 &ldquo;+&rdquo;号 添加节点，类型选择 Subscribe</li><li>完整复制本页上方 &ldquo;订阅服务&rdquo; 处地址，将其粘贴至 &ldquo;URL&rdquo;栏，点击右上角 &ldquo;完成&rdquo;</li><li>左划新增的服务器订阅，点击 &ldquo;更新&rdquo;</li><li>选定合适服务器节点，点击右上角连接开关，屏幕上方状态栏出现&ldquo;VPN&rdquo;图标</li><li>当进行海外游戏时请将 Shadowrocket &ldquo;首页&rdquo; 页面中的 &ldquo;全局路由&rdquo; 切换至 &ldquo;代理&rdquo;，并确保&ldquo;设置&rdquo;页面中的&ldquo;UDP&rdquo;已开启转发</li></ol>',
                ], 96 => [
                    'Mac',
                    '<ol><li><a href="clients/ShadowsocksX-NG-R8-1.4.6.dmg" target="_blank" rel="noopener">点击此处</a>下载客户端并启动</li><li>点击状态栏纸飞机 -&gt; 服务器 -&gt; 编辑订阅</li><li>点击窗口左下角 &ldquo;+&rdquo;号 新增订阅，完整复制本页上方&ldquo;订阅服务&rdquo;处地址，将其粘贴至&ldquo;订阅地址&rdquo;栏，点击右下角&ldquo;OK&rdquo;</li><li>点击纸飞机 -&gt; 服务器 -&gt; 手动更新订阅</li><li>点击纸飞机 -&gt; 服务器，选定合适服务器</li><li>点击纸飞机 -&gt; 打开Shadowsocks</li><li>点击纸飞机 -&gt; PAC自动模式</li><li>点击纸飞机 -&gt; 代理设置-&gt;从 GFW List 更新 PAC</li><li>打开系统偏好设置 -&gt; 网络，在窗口左侧选定显示为&ldquo;已连接&rdquo;的网络，点击右下角&ldquo;高级...&rdquo;</li><li>切换至&ldquo;代理&rdquo;选项卡，勾选&ldquo;自动代理配置&rdquo;和&ldquo;不包括简单主机名&rdquo;，点击右下角&ldquo;好&rdquo;，再次点击右下角&ldquo;应用&rdquo;</li></ol>',
                ], 95 => [
                    'Linux', '<ol><li><a href="clients/Shadowsocks-qt5-3.0.1.zip" target="_blank" rel="noopener">点击此处</a>下载客户端并启动</li><li>单击状态栏小飞机，找到服务器 -&gt; 编辑订阅，复制黏贴订阅地址</li><li>更新订阅设置即可</li></ol>',
                ],
            ],
        ];
        foreach ($articles as $category => $article) {
            foreach ($article as $sort => $body) {
                Article::create(['title' => $body[0], 'content' => $body[1], 'type' => 1, 'sort' => $sort, 'language' => 'zh_CN', 'category' => $category]);
            }
        }
        Article::create(['title' => 'Welcome！ 欢迎！', 'content' => 'Welcome to ProxyPanel!<br> 欢迎使用ProxyPanel！', 'type' => 2, 'language' => 'zh_CN']);
    }
}
