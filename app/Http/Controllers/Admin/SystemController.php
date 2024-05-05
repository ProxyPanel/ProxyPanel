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
            'payments' => $this->getPayment(),
            'captcha' => $this->getCaptcha(),
            'ddns_labels' => (new DDNS())->getLabels(),
        ], Config::pluck('value', 'name')->toArray()));
    }

    private function getPayment(): array
    { // 获取已经完成配置的支付渠道
        if (sysConfig('f2fpay_app_id') && sysConfig('f2fpay_private_key') && sysConfig('f2fpay_public_key')) {
            $payment[] = 'f2fpay';
        }
        if (sysConfig('codepay_url') && sysConfig('codepay_id') && sysConfig('codepay_key')) {
            $payment[] = 'codepay';
        }
        if (sysConfig('epay_url') && sysConfig('epay_mch_id') && sysConfig('epay_key')) {
            $payment[] = 'epay';
        }
        if (sysConfig('payjs_mch_id') && sysConfig('payjs_key')) {
            $payment[] = 'payjs';
        }
        if (sysConfig('bitpay_secret')) {
            $payment[] = 'bitpayx';
        }
        if (sysConfig('paypal_client_id') && sysConfig('paypal_client_secret') && sysConfig('paypal_app_id')) {
            $payment[] = 'paypal';
        }
        if (sysConfig('stripe_public_key') && sysConfig('stripe_secret_key')) {
            $payment[] = 'stripe';
        }
        if (sysConfig('paybeaver_app_id') && sysConfig('paybeaver_app_secret')) {
            $payment[] = 'paybeaver';
        }
        if (sysConfig('theadpay_mchid') && sysConfig('theadpay_key')) {
            $payment[] = 'theadpay';
        }

        return $payment ?? [];
    }

    private function getCaptcha(): bool
    {
        return sysConfig('captcha_secret') && sysConfig('captcha_key');
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
                if (Config::find('website_home_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#other')->with('successMsg', '更新成功');
                }
            }
            if ($request->hasFile('website_logo')) { // 站内LOGO
                $validator = validator()->make($request->all(), ['website_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

                if ($validator->fails()) {
                    return redirect()->route('admin.system.index', '#other')->withErrors($validator->errors());
                }
                $file = $request->file('website_logo');
                $file->move('uploads/logo', $file->getClientOriginalName());
                if (Config::findOrFail('website_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                    return redirect()->route('admin.system.index', '#other')->with('successMsg', '更新成功');
                }
            }

            return redirect()->route('admin.system.index', '#other')->withErrors('更新失败');
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
                    return redirect()->route('admin.system.index', '#payment')->with('successMsg', '更新成功');
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
                    return redirect()->route('admin.system.index', '#payment')->with('successMsg', '更新成功');
                }
            }

            return redirect()->route('admin.system.index', '#payment')->withErrors('更新失败');
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
        if ($value !== null && in_array($name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay'], true) && ! in_array($value, $this->getPayment(), true)) {
            return Response::json(['status' => 'fail', 'message' => '请先完善该支付渠道的必要参数！']);
        }

        if ($value > 1 && $name === 'is_captcha' && ! $this->getCaptcha()) {
            return Response::json(['status' => 'fail', 'message' => '请先完善验证码的必要参数！']);
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
                return Response::json(['status' => 'fail', 'message' => '演示环境禁止修改该配置']);
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
            return Response::json(['status' => 'success', 'message' => trans('common.update_action', ['action' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.update_action', ['action' => trans('common.failed')])]);
    }

    public function sendTestNotification(): JsonResponse  // 推送通知测试
    {
        $data = ['这是测试的标题', 'ProxyPanel测试内容'];
        switch (request('channel')) {
            case 'serverChan':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [ServerChanChannel::class]);
                break;
            case 'bark':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [BarkChannel::class]);
                break;
            case 'telegram':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [TelegramChannel::class]);
                break;
            case 'weChat':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [WeChatChannel::class]);
                break;
            case 'tgChat':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [TgChatChannel::class]);
                break;
            case 'pushPlus':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [PushPlusChannel::class]);
                break;
            case 'iYuu':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [iYuuChannel::class]);
                break;
            case 'pushDeer':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [PushDeerChannel::class]);
                break;
            case 'dingTalk':
                Notification::sendNow(Auth::getUser(), new Custom($data[0], $data[1]), [DingTalkChannel::class]);
                break;
            default:
                return Response::json(['status' => 'fail', 'message' => '未知渠道']);
        }

        return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
    }
}
