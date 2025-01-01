<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): View
    { // 工单列表
        $query = Ticket::where(static function ($query) {
            $query->whereAdminId(auth()->id())->orwhere('admin_id');
        })->with('user');

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        return view('admin.ticket.index', ['ticketList' => $query->orderBy('status')->latest()->paginate(10)->appends($request->except('page'))]);
    }

    public function store(TicketRequest $request): JsonResponse
    { // 创建工单
        $data = $request->validated();
        $user = User::find($data['uid']) ?: User::whereUsername($data['username'])->first();

        if ($user === auth()->user()) {
            return response()->json(['status' => 'fail', 'message' => trans('admin.ticket.self_send')]);
        }

        if ($ticket = Ticket::create(['user_id' => $user->id, 'admin_id' => auth()->id(), 'title' => $data['title'], 'content' => clean($data['content'])])) {
            $user->notify(new TicketCreated($ticket, route('ticket.edit', $ticket)));

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.create')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.create')])]);
    }

    public function edit(Ticket $ticket): View
    { // 回复
        return view('admin.ticket.reply', [
            'ticket' => $ticket,
            'user' => $ticket->user,
            'replyList' => $ticket->reply()->with('ticket:id,status', 'admin:id,username,qq', 'user:id,username,qq')->oldest()->get(),
        ]);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    { // 回复工单
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

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.ticket.reply')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('user.ticket.reply')])]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    { // 关闭工单
        if (! $ticket->close()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.close')])]);
        }
        // 通知用户
        if (sysConfig('ticket_closed_notification')) {
            $ticket->user->notify(new TicketClosed($ticket->id, $ticket->title, route('ticket.edit', $ticket), \request('reason'), true));
        }

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.close')])]);
    }
}
