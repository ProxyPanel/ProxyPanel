<?php

namespace App\Http\Controllers\Admin;

use App\Components\PushNotification;
use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Response;

class SystemController extends Controller
{
    // 系统设置
    public function index()
    {
        $view = Config::pluck('value', 'name')->toArray();
        $view['labelList'] = Label::orderByDesc('sort')->orderBy('id')->get();

        return view('admin.config.system', $view);
    }

    // 设置系统扩展信息，例如客服、统计代码
    public function setExtend(Request $request): RedirectResponse
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
            if ($ret && Config::find('website_logo')->update(['value' => 'uploads/logo/'.$file->getClientOriginalName()])) {
                return redirect()->route('admin.system.index', '#other')->with('successMsg', '更新成功');
            }
        }

        return redirect()->route('admin.system.index', '#other')->withErrors('更新失败');
    }

    // 设置某个配置项
    public function setConfig(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $value = $request->input('value');

        if (! $name) {
            return Response::json(['status' => 'fail', 'message' => '设置失败：请求参数异常']);
        }

        // 屏蔽异常配置
        if (! in_array($name, Config::pluck('name')->toArray(), true)) {
            return Response::json(['status' => 'fail', 'message' => '设置失败：配置不存在']);
        }

        // 如果开启用户邮件重置密码，则先设置网站名称和网址
        if ($value !== '0' && in_array($name, ['is_reset_password', 'is_activate_account', 'expire_warning', 'traffic_warning'], true)) {
            $config = Config::find('website_name');
            if (! $config->value) {
                return Response::json(['status' => 'fail', 'message' => '设置失败：启用该配置需要先设置【网站名称】']);
            }

            $config = Config::find('website_url');
            if (! $config->value) {
                return Response::json(['status' => 'fail', 'message' => '设置失败：启用该配置需要先设置【网站地址】']);
            }
        }

        // 支付设置判断
        if ($value !== null && in_array($name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'], true)) {
            switch ($value) {
                case 'f2fpay':
                    if (! sysConfig('f2fpay_app_id') || ! sysConfig('f2fpay_private_key') || ! sysConfig('f2fpay_public_key')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【支付宝F2F】必要参数']);
                    }
                    break;
                case 'codepay':
                    if (! sysConfig('codepay_url') || ! sysConfig('codepay_id') || ! sysConfig('codepay_key')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【码支付】必要参数']);
                    }
                    break;
                case 'epay':
                    if (! sysConfig('epay_url') || ! sysConfig('epay_mch_id') || ! sysConfig('epay_key')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【易支付】必要参数']);
                    }
                    break;
                case 'payjs':
                    if (! sysConfig('payjs_mch_id') || ! sysConfig('payjs_key')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【PayJs】必要参数']);
                    }
                    break;
                case 'bitpayx':
                    if (! sysConfig('bitpay_secret')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【麻瓜宝】必要参数']);
                    }
                    break;
                case 'paypal':
                    if (! sysConfig('paypal_username') || ! sysConfig('paypal_password') || ! sysConfig('paypal_secret')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【PayPal】必要参数']);
                    }
                    break;
                case 'stripe':
                    if (! sysConfig('stripe_public_key') || ! sysConfig('stripe_secret_key')) {
                        return Response::json(['status' => 'fail', 'message' => '请先设置【Stripe】必要参数']);
                    }
                    break;
                default:
                    return Response::json(['status' => 'fail', 'message' => '未知支付渠道']);
            }
        }

        // 演示环境禁止修改特定配置项
        if (config('app.demo')) {
            $denyConfig = [
                'website_url',
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
            $value = (int) $value / 100;
        }

        // 更新配置
        Config::find($name)->update(['value' => $value]);

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 推送通知测试
    public function sendTestNotification(): JsonResponse
    {
        if (sysConfig('is_notification')) {
            $result = PushNotification::send('这是测试的标题', 'ProxyPanel测试内容');
            if ($result === false) {
                return Response::json(['status' => 'fail', 'message' => '发送失败，请重新尝试！']);
            }
            switch (sysConfig('is_notification')) {
                case 'serverChan':
                    if (! $result['errno']) {
                        return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
                    }

                    return Response::json(['status' => 'fail', 'message' => $result ? $result['errmsg'] : '未知']);
                case 'bark':
                    if ($result['code'] === 200) {
                        return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
                    }

                    return Response::json(['status' => 'fail', 'message' => $result['message']]);
                default:
            }
        }

        return Response::json(['status' => 'fail', 'message' => '请先选择【日志通知】渠道']);
    }
}
