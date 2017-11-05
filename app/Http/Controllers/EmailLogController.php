<?php

namespace App\Http\Controllers;

use App\Http\Models\EmailLog;
use Illuminate\Http\Request;
use Response;

/**
 * 邮件发送日志控制器
 * Class LoginController
 * @package App\Http\Controllers
 */
class EmailLogController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 邮件发送日志列表
    public function logList(Request $request)
    {
        $view['list'] = EmailLog::query()->with('user')->orderBy('id', 'desc')->paginate(10);

        return Response::view('emailLog/logList', $view);
    }

}
