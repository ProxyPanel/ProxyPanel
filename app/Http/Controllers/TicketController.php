<?php

namespace App\Http\Controllers;

use App\Http\Models\Ticket;
use App\Http\Models\TicketReply;
use Illuminate\Http\Request;
use Response;

/**
 * 工单控制器
 * Class TicketController
 * @package App\Http\Controllers
 */
class TicketController extends Controller
{
    // 工单列表
    public function ticketList(Request $request)
    {
        $view['ticketList'] = Ticket::query()->orderBy('id', 'desc')->paginate(10);

        return Response::view('ticket/ticketList', $view);
    }

    // 回复工单
    public function replyTicket(Request $request)
    {
        $id = $request->get('id');

        $user = $request->session()->get('user');

        if ($request->method() == 'POST') {
            $content = clean($request->get('content'));

            $obj = new TicketReply();
            $obj->ticket_id = $id;
            $obj->user_id = $user['id'];
            $obj->content = $content;
            $obj->created_at = date('Y-m-d H:i:s');
            $obj->save();

            if ($obj->id) {
                // 将工单置为已回复
                Ticket::query()->where('id', $id)->update(['status' => 1]);

                return Response::json(['status' => 'success', 'data' => '', 'message' => '回复成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复失败']);
            }
        } else {
            $view['ticket'] = Ticket::query()->where('id', $id)->with('user')->first();
            $view['replyList'] = TicketReply::query()->where('ticket_id', $id)->with('user')->orderBy('id', 'asc')->get();

            return Response::view('ticket/replyTicket', $view);
        }
    }

    // 关闭工单
    public function closeTicket(Request $request)
    {
        $id = $request->get('id');

        $ret = Ticket::query()->where('id', $id)->update(['status' => 2]);
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '关闭成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
        }
    }

}
