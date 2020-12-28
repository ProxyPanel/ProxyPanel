<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\closeTicket;
use App\Mail\replyTicket;
use App\Models\Ticket;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Mail;
use Response;

/**
 * 工单控制器.
 *
 * Class TicketController
 */
class TicketController extends Controller
{
    // 工单列表
    public function index(Request $request)
    {
        $email = $request->input('email');

        $query = Ticket::whereAdminId(Auth::id())->orwhere('admin_id');

        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        return view('admin.ticket.index', ['ticketList' => $query->latest()->paginate(10)->appends($request->except('page'))]);
    }

    // 创建工单
    public function store(Request $request)
    {
        $id = $request->input('id');
        $email = $request->input('email');
        $title = $request->input('title');
        $content = $request->input('content');

        $user = User::find($id) ?: User::whereEmail($email)->first();

        if (! $user) {
            return Response::json(['status' => 'fail', 'message' => '用户不存在']);
        }

        if ($user === Auth::user()) {
            return Response::json(['status' => 'fail', 'message' => '不能对自己发起工单']);
        }

        if (empty($title) || empty($content)) {
            return Response::json(['status' => 'fail', 'message' => '请输入标题和内容']);
        }

        $obj = new Ticket();
        $obj->user_id = $user->id;
        $obj->admin_id = Auth::id();
        $obj->title = $title;
        $obj->content = $content;

        if ($obj->save()) {
            return Response::json(['status' => 'success', 'message' => '工单创建成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '工单创建失败']);
    }

    // 回复
    public function edit(Ticket $ticket)
    {
        return view('admin.ticket.reply', [
            'ticket' => $ticket,
            'replyList' => $ticket->reply()->oldest()->get(),
        ]);
    }

    // 回复工单
    public function update(Request $request, Ticket $ticket)
    {
        $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

        if ($ticket->reply()->create(['admin_id' => Auth::id(), 'content' => $content])) {
            // 将工单置为已回复
            $ticket->update(['status' => 1]);

            $title = '工单回复提醒';
            $content = '标题：'.$ticket->title.'<br>管理员回复：'.$content;

            // 发通知邮件
            $logId = Helpers::addNotificationLog($title, $content, 1, $ticket->user->email);
            Mail::to($ticket->user->email)->send(new replyTicket($logId, $title, $content));

            return Response::json(['status' => 'success', 'message' => '回复成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '回复失败']);
    }

    // 关闭工单
    public function destroy(Ticket $ticket)
    {
        if (! $ticket->close()) {
            return Response::json(['status' => 'fail', 'message' => '关闭失败']);
        }

        $title = '工单关闭提醒';
        $content = '工单【'.$ticket->title.'】已关闭';

        // 发邮件通知用户
        $logId = Helpers::addNotificationLog($title, $content, 1, $ticket->user->email);
        Mail::to($ticket->user->email)->send(new closeTicket($logId, $title, $content));

        return Response::json(['status' => 'success', 'message' => '关闭成功']);
    }
}
