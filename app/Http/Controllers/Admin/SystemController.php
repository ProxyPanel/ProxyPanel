<?php

namespace App\Http\Controllers\Admin;

use App\Channels\BarkChannel;
use App\Channels\ServerChanChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SystemRequest;
use App\Models\Config;
use App\Notifications\Custom;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Notification;
use Request;
use Response;

class SystemController extends Controller
{
    // 系统设置
    public function index()
    {
        return view('admin.config.system', array_merge(['payments' => $this->getPayment(), 'captcha' => $this->getCaptcha()], Config::pluck('value', 'name')->toArray()));
    }

    private function getPayment() // 获取已经完成配置的支付渠道
    {
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
        if (sysConfig('paypal_username') && sysConfig('paypal_password') && sysConfig('paypal_secret')) {
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

    private function getCaptcha()
    {
        return sysConfig('captcha_secret') && sysConfig('captcha_key');
    }

    public function setExtend(Request $request): RedirectResponse  // 设置系统扩展信息，例如客服、统计代码
    {
        if ($request->hasFile('website_home_logo')) {
            $validator = validator()->make($request->all(), ['website_home_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            if ($validator->fails()) {
                return redirect()->route('admin.system.index', '#other')->withErrors($validator->errors());
            }
            $file = $request->file('website_home_logo');
            $ret = $file->move('uploads/logo', $file->getClientOriginalName());
            if ($ret && Config::find('website_home_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                return redirect()->route('admin.system.index', '#other')->with('successMsg', '更新成功');
            }
        }

        // 站内LOGO
        if ($request->hasFile('website_logo')) {
            $validator = validator()->make($request->all(), ['website_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            if ($validator->fails()) {
                return redirect()->route('admin.system.index', '#other')->withErrors($validator->errors());
            }
            $file = $request->file('website_logo');
            $ret = $file->move('uploads/logo', $file->getClientOriginalName());
            if ($ret && Config::findOrFail('website_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                return redirect()->route('admin.system.index', '#other')->with('successMsg', '更新成功');
            }
        }

        return redirect()->route('admin.system.index', '#other')->withErrors('更新失败');
    }

    public function setConfig(SystemRequest $request): JsonResponse // 设置某个配置项
    {
        $name = $request->input('name');
        $value = $request->input('value');

        if (empty($value)) { // 关闭 或 空值 自动设NULL，减少系统设置存储
            $value = null;
        }

        // 支付设置判断
        if ($value !== null && in_array($name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'], true) && ! in_array($value, $this->getPayment(), true)) {
            return Response::json(['status' => 'fail', 'message' => '请先完善该支付渠道的必要参数！']);
        }

        if ($value > 1 && $name === 'is_captcha' && ! $this->getCaptcha()) {
            return Response::json(['status' => 'fail', 'message' => '请先完善验证码的必要参数！']);
        }

        // 演示环境禁止修改特定配置项
        if (config('app.demo')) {
            $denyConfig = [
                'website_url',
                'is_captcha',
                'min_rand_traffic',
                'max_rand_traffic',
                'push_bear_send_key',
                'push_bear_qrcode',
                'forbid_mode',
                'website_security_code',
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
        switch (request('channel')) {
            case 'serverChan':
                Notification::sendNow(ServerChanChannel::class, new Custom('这是测试的标题', 'ProxyPanel测试内容'));
                break;
            case 'bark':
                Notification::sendNow(BarkChannel::class, new Custom('这是测试的标题', 'ProxyPanel测试内容'));
                break;
            default:
                return Response::json(['status' => 'fail', 'message' => '未知渠道']);
        }

        return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
    }
}
