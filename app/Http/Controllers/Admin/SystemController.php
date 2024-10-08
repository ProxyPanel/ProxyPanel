<?php

namespace App\Http\Controllers\Admin;

use App\Channels\BarkChannel;
use App\Channels\DingTalkChannel;
use App\Channels\iYuuChannel;
use App\Channels\PushDeerChannel;
use App\Channels\PushPlusChannel;
use App\Channels\ServerChanChannel;
use App\Channels\TgChatChannel;
use App\Channels\WeChatChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SystemRequest;
use App\Models\Config;
use App\Notifications\Custom;
use App\Services\TelegramService;
use App\Utils\DDNS;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Notification;
use NotificationChannels\Telegram\TelegramChannel;
use Response;

class SystemController extends Controller
{
    // 系统设置
    public function index()
    {
        return view('admin.config.system', array_merge([
            'payments' => $this->getPayments(),
            'captcha' => $this->getCaptcha(),
            'channels' => $this->getNotifyChannels(),
            'ddns_labels' => (new DDNS)->getLabels(),
        ], Config::pluck('value', 'name')->toArray()));
    }

    private function getPayments(): array
    {
        $paymentConfigs = [ // 支付渠道及其所需配置项映射
            'f2fpay' => ['f2fpay_app_id', 'f2fpay_private_key', 'f2fpay_public_key'],
            'codepay' => ['codepay_url', 'codepay_id', 'codepay_key'],
            'epay' => ['epay_url', 'epay_mch_id', 'epay_key'],
            'payjs' => ['payjs_mch_id', 'payjs_key'],
            'bitpayx' => ['bitpay_secret'],
            'paypal' => ['paypal_client_id', 'paypal_client_secret', 'paypal_app_id'],
            'stripe' => ['stripe_public_key', 'stripe_secret_key'],
            'paybeaver' => ['paybeaver_app_id', 'paybeaver_app_secret'],
            'theadpay' => ['theadpay_mchid', 'theadpay_key'],
        ];

        $payment = [];

        // 遍历映射，检查配置项是否存在
        foreach ($paymentConfigs as $paymentName => $configKeys) {
            $allConfigsExist = array_reduce($configKeys, function ($carry, $configKey) {
                return $carry && sysConfig($configKey);
            }, true);

            if ($allConfigsExist) {
                $payment[] = $paymentName;
            }
        }

        return $payment;
    }

    private function getCaptcha(): bool
    {
        return sysConfig('captcha_secret') && sysConfig('captcha_key');
    }

    private function getNotifyChannels(): array
    {
        $configs = [ // 支付渠道及其所需配置项映射
            'bark' => ['bark_key'],
            'dingTalk' => ['dingTalk_access_token'],
            'iYuu' => ['iYuu_token'],
            'pushDear' => ['pushDeer_key'],
            'pushPlus' => ['pushplus_token'],
            'serverChan' => ['server_chan_key'],
            'telegram' => ['telegram_token'],
            'tgChat' => ['tg_chat_token'],
            'weChat' => ['wechat_cid', 'wechat_aid', 'wechat_secret', 'wechat_token', 'wechat_encodingAESKey'],
        ];

        $channels = ['database', 'mail'];

        // 遍历映射，检查配置项是否存在
        foreach ($configs as $channel => $configKeys) {
            $allConfigsExist = array_reduce($configKeys, static function ($carry, $configKey) {
                return $carry && sysConfig($configKey);
            }, true);

            if ($allConfigsExist) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    public function setExtend(Request $request): RedirectResponse  // 设置涉及到上传的设置
    {
        if ($request->hasAny(['website_home_logo', 'website_home_logo'])) { // 首页LOGO
            if ($request->hasFile('website_home_logo')) {
                $validator = validator()->make($request->all(), ['website_home_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

                if ($validator->fails()) {
                    return redirect()->route('admin.system.index', '#other')->withErrors($validator->errors());
                }
                $file = $request->file('website_home_logo');
                $file->move('uploads/logo', $file->getClientOriginalName());
                if (Config::findOrNew('website_home_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#other')->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
                }
            }
            if ($request->hasFile('website_logo')) { // 站内LOGO
                $validator = validator()->make($request->all(), ['website_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

                if ($validator->fails()) {
                    return redirect()->route('admin.system.index', '#other')->withErrors($validator->errors());
                }
                $file = $request->file('website_logo');
                $file->move('uploads/logo', $file->getClientOriginalName());
                if (Config::findOrNew('website_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#other')->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
                }
            }

            return redirect()->route('admin.system.index', '#other')->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
        }

        if ($request->hasAny(['alipay_qrcode', 'wechat_qrcode'])) {
            if ($request->hasFile('alipay_qrcode')) {
                $validator = validator()->make($request->all(), ['alipay_qrcode' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

                if ($validator->fails()) {
                    return redirect()->route('admin.system.index', '#payment')->withErrors($validator->errors());
                }
                $file = $request->file('alipay_qrcode');
                $file->move('uploads/images', $file->getClientOriginalName());
                if (Config::find('alipay_qrcode')->update(['value' => 'uploads/images/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#payment')->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
                }
            }

            if ($request->hasFile('wechat_qrcode')) { // 站内LOGO
                $validator = validator()->make($request->all(), ['wechat_qrcode' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

                if ($validator->fails()) {
                    return redirect()->route('admin.system.index', '#payment')->withErrors($validator->errors());
                }
                $file = $request->file('wechat_qrcode');
                $file->move('uploads/images', $file->getClientOriginalName());
                if (Config::findOrFail('wechat_qrcode')->update(['value' => 'uploads/images/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#payment')->with('successMsg', trans('common.success_item', ['attribute' => trans('common.update')]));
                }
            }

            return redirect()->route('admin.system.index', '#payment')->withErrors(trans('common.failed_item', ['attribute' => trans('common.update')]));
        }

        return redirect()->route('admin.system.index');
    }

    public function setConfig(SystemRequest $request): JsonResponse // 设置某个配置项
    {
        $name = $request->input('name');
        $value = $request->input('value');

        if (empty($value) || $value === 'NaN') { // 关闭 或 空值 自动设NULL，减少系统设置存储
            $value = null;
        }

        // 支付设置判断
        if ($value !== null && in_array($name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay'], true) && ! in_array($value, $this->getPayments(), true)) {
            return Response::json(['status' => 'fail', 'message' => trans('admin.system.params_required', ['attribute' => trans('admin.system.payment.attribute')])]);
        }

        if ($value > 1 && $name === 'is_captcha' && ! $this->getCaptcha()) {
            return Response::json(['status' => 'fail', 'message' => trans('admin.system.params_required', ['attribute' => trans('auth.captcha.attribute')])]);
        }

        // 演示环境禁止修改特定配置项
        if (config('app.env') === 'demo') {
            $denyConfig = [
                'website_url',
                'is_captcha',
                'min_rand_traffic',
                'max_rand_traffic',
                'forbid_mode',
                'website_security_code',
                'website_security_code',
                'username_type',
            ];

            if (in_array($name, $denyConfig, true)) {
                return Response::json(['status' => 'fail', 'message' => trans('admin.system.demo_restriction')]);
            }
        }

        // 如果是返利比例，则需要除100
        if ($name === 'referral_percent') {
            $value /= 100;
        }

        // 设置TG机器人
        if ($name === 'telegram_token' && $value) {
            $telegramService = new TelegramService($value);
            $telegramService->getMe();
            $telegramService->setWebhook(rtrim(sysConfig('website_url'), '/').'/api/telegram/webhook?access_token='.md5($value));
        }

        // 更新配置
        if (Config::findOrFail($name)->update(['value' => $value])) {
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.update')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.update')])]);
    }

    public function sendTestNotification(): JsonResponse  // 推送通知测试
    {
        $channels = [
            'serverChan' => ServerChanChannel::class,
            'bark' => BarkChannel::class,
            'telegram' => TelegramChannel::class,
            'weChat' => WeChatChannel::class,
            'tgChat' => TgChatChannel::class,
            'pushPlus' => PushPlusChannel::class,
            'iYuu' => iYuuChannel::class,
            'pushDeer' => PushDeerChannel::class,
            'dingTalk' => DingTalkChannel::class,
        ];

        $selectedChannel = request('channel');

        if (! array_key_exists($selectedChannel, $channels)) {
            return Response::json(['status' => 'fail', 'message' => trans('admin.system.notification.test.unknown_channel')]);
        }

        Notification::sendNow(Auth::getUser(), new Custom(trans('admin.system.notification.test.title'), sysConfig('website_name').' '.trans('admin.system.notification.test.content')), [$channels[$selectedChannel]]);

        return Response::json(['status' => 'success', 'message' => trans('admin.system.notification.test.success')]);
    }
}
