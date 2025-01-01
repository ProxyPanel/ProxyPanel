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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class TicketController extends Controller
{
    // 工单列表
    public function index(Request $request)
    {
        $query = Ticket::where(function ($query) {
            $query->whereAdminId(Auth::id())->orwhere('admin_id');
        })->with('user');

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.ticket.index', ['ticketList' => $query->orderBy('status')->latest()->paginate(10)->appends($request->except('page'))]);
    }

    // 创建工单
    public function store(TicketRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::find($data['uid']) ?: User::whereUsername($data['username'])->first();

        if ($user === Auth::user()) {
            return Response::json(['status' => 'fail', 'message' => trans('admin.ticket.self_send')]);
        }

        if ($ticket = Ticket::create(['user_id' => $user->id, 'admin_id' => auth()->id(), 'title' => $data['title'], 'content' => clean($data['content'])])) {
            $user->notify(new TicketCreated($ticket, route('ticket.edit', $ticket)));

            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.create')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.create')])]);
    }

    // 回复
    public function edit(Ticket $ticket)
    {
        return view('admin.ticket.reply', [
            'ticket' => $ticket,
            'user' => $ticket->user,
            'replyList' => $ticket->reply()->with('ticket:id,status', 'admin:id,username,qq', 'user:id,username,qq')->oldest()->get(),
        ]);
    }

    // 回复工单
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

        $reply = $ticket->reply()->create(['admin_id' => Auth::id(), 'content' => $content]);
        if ($reply) {
            // 将工单置为已回复
            if ($ticket->status !== 1) {
                $ticket->update(['status' => 1]);
            }

            // 通知用户
            if (sysConfig('ticket_replied_notification')) {
                $ticket->user->notify(new TicketReplied($reply, route('ticket.edit', $ticket), true));
            }

            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.ticket.reply')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('user.ticket.reply')])]);
    }

    // 关闭工单
    public function destroy(Ticket $ticket): JsonResponse
    {
        if (! $ticket->close()) {
            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.close')])]);
        }
        // 通知用户
        if (sysConfig('ticket_closed_notification')) {
            $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('ticket.edit', $ticket), \request('reason'), true));
        }

        return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.close')])]);
    }
}
