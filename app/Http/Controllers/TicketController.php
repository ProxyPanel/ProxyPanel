<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\Ticket;
use App\Http\Models\TicketReply;
use App\Mail\closeTicket;
use App\Mail\replyTicket;
use Illuminate\Http\Request;
use Response;
use Mail;
use Auth;

/**
 * 工单控制器
 *
 * Class TicketController
 *
 * @package App\Http\Controllers
 */
class TicketController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 工单列表
    public function ticketList(Request $request)
    {
        $view['ticketList'] = Ticket::query()->orderBy('id', 'desc')->paginate(10);

        return Response::view('ticket.ticketList', $view);
    }

    // 回复工单
    public function replyTicket(Request $request)
    {
        $id = $request->get('id');

        if ($request->isMethod('POST')) {
            $content = clean($request->get('content'));
            $content = str_replace("eval", "", str_replace("atob", "", $content));
            $content = substr($content, 0, 300);

            $obj = new TicketReply();
            $obj->ticket_id = $id;
            $obj->user_id = Auth::user()->id;
            $obj->content = $content;
            $obj->save();

            if ($obj->id) {
                // 将工单置为已回复
                $ticket = Ticket::query()->with(['user'])->where('id', $id)->first();
                $ticket->status = 1;
                $ticket->save();


                $title = "工单回复提醒";
                $content = "标题：" . $ticket->title . "<br>管理员回复：" . $content;

                // 发通知邮件
                if (!Auth::user()->is_admin) {
                    if (self::$systemConfig['crash_warning_email']) {
                        $logId = Helpers::addEmailLog(self::$systemConfig['crash_warning_email'], $title, $content);
                        Mail::to(self::$systemConfig['crash_warning_email'])->send(new replyTicket($logId, $title, $content));
                    }
                } else {
                    $logId = Helpers::addEmailLog($ticket->user->username, $title, $content);
                    Mail::to($ticket->user->username)->send(new replyTicket($logId, $title, $content));
                }

                // 通过ServerChan发微信消息提醒管理员
                if (!Auth::user()->is_admin) {
                    ServerChan::send($title, $content);
                }

                return Response::json(['status' => 'success', 'data' => '', 'message' => '回复成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复失败']);
            }
        } else {
            $view['ticket'] = Ticket::query()->where('id', $id)->with('user')->first();
            $view['replyList'] = TicketReply::query()->where('ticket_id', $id)->with('user')->orderBy('id', 'asc')->get();

            return Response::view('ticket.replyTicket', $view);
        }
    }

    // 关闭工单
    public function closeTicket(Request $request)
    {
        $id = $request->get('id');

        $ticket = Ticket::query()->with(['user'])->where('id', $id)->first();
        if (!$ticket) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
        }

        $ticket->status = 2;
        $ret = $ticket->save();
        if (!$ret) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
        }

        $title = "工单关闭提醒";
        $content = "工单【" . $ticket->title . "】已关闭";

        // 发邮件通知用户
        $logId = Helpers::addEmailLog($ticket->user->username, $title, $content);
        Mail::to($ticket->user->username)->send(new closeTicket($logId, $title, $content));

        return Response::json(['status' => 'success', 'data' => '', 'message' => '关闭成功']);
    }

}
