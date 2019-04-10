<?php
namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Models\Marketing;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Response;
use Log;
use DB;

/**
 * 促销控制器
 *
 * Class MarketingController
 *
 * @package App\Http\Controllers
 */
class MarketingController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 邮件群发消息列表
    public function emailList(Request $request)
    {
        $view['list'] = Marketing::query()->where('type', 1)->paginate(15);

        return Response::view('marketing.emailList', $view);
    }

    private function addMarketing($type = 1, $title = '', $content = '', $status = 1, $error = '', $receiver = '')
    {
        $marketing = new Marketing();
        $marketing->type = $type;
        $marketing->receiver = $receiver;
        $marketing->title = $title;
        $marketing->content = $content;
        $marketing->error = $error;
        $marketing->status = $status;

        return $marketing->save();
    }
}