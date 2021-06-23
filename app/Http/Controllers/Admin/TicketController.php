<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Auth;
use Illuminate\Http\Request;
use Response;

class TicketController extends Controller
{
    // 工单列表
    public function index(Request $request)
    {
        $query = Ticket::where(function ($query) {
            $query->whereAdminId(Auth::id())->orwhere('admin_id');
        });

        $request->whenFilled('email', function ($email) use ($query) {
            $query->whereHas('user', function ($query) use ($email) {
                $query->where('email', 'like', "%{$email}%");
            });
        });

        return view('admin.ticket.index', ['ticketList' => $query->latest()->paginate(10)->appends($request->except('page'))]);
    }

    // 创建工单
    public function store(TicketRequest $request)
    {
        $data = $request->validated();
        $user = User::find($data['id']) ?: User::whereEmail($data['email'])->first();

        if ($user === Auth::user()) {
            return Response::json(['status' => 'fail', 'message' => '不能对自己发起工单']);
        }

        if ($ticket = Ticket::create(['user_id' => $user->id, 'admin_id' => auth()->id(), 'title' => $data['title'], 'content' => clean($data['content'])])) {
            $user->notify(new TicketCreated($ticket, route('replyTicket', $ticket)));

            return Response::json(['status' => 'success', 'message' => '工单创建成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '工单创建失败']);
    }

    // 回复
    public function edit(Ticket $ticket)
    {
        return view('admin.ticket.reply', [
            'ticket'    => $ticket,
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

            // 通知用户
            if (sysConfig('ticket_replied_notification')) {
                $ticket->user->notify(new TicketReplied($ticket, route('replyTicket', $ticket), true));
            }

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
        // 通知用户
        if (sysConfig('ticket_closed_notification')) {
            $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('replyTicket', $ticket), \request('reason'), true));
        }

        return Response::json(['status' => 'success', 'message' => '关闭成功']);
    }
}
