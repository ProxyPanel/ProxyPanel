<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Notification;

class TicketController extends Controller
{
    public function index(Request $request): View
    { // 工单
        return view('user.tickets', [
            'tickets' => auth()->user()->tickets()->latest()->paginate(10)->appends($request->except('page')),
        ]);
    }

    public function store(Request $request): JsonResponse
    { // 添加工单
        $title = $request->input('title');
        $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

        if (empty($title) || empty($content)) {
            return response()->json([
                'status' => 'fail', 'message' => trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.title')).'&'.ucfirst(trans('validation.attributes.content'))]),
            ]);
        }

        if ($ticket = auth()->user()->tickets()->create(compact('title', 'content'))) {
            // 通知相关管理员
            Notification::send(User::find(1), new TicketCreated($ticket, route('admin.ticket.edit', $ticket)));
        }

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.submit')])]);
    }

    public function edit(Ticket $ticket): View
    { // 回复工单
        return view('user.replyTicket', [
            'ticket' => $ticket,
            'replyList' => $ticket->reply()->with('ticket:id,status', 'admin:id,username,qq', 'user:id,username,qq')->oldest()->get(),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

        if (empty($content)) {
            return response()->json([
                'status' => 'fail', 'message' => trans('validation.required', ['attribute' => ucfirst(trans('validation.attributes.title')).'&'.ucfirst(trans('validation.attributes.content'))]),
            ]);
        }

        $reply = $ticket->reply()->create(['user_id' => auth()->id(), 'content' => $content]);
        if ($reply) {
            // 重新打开工单
            if (in_array($ticket->status, [1, 2], true)) {
                $ticket->update(['status' => 0]);
            }

            // 通知相关管理员
            Notification::send(User::find(1), new TicketReplied($reply, route('admin.ticket.edit', $ticket)));

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('user.ticket.reply')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('user.ticket.reply')])]);
    }

    public function close(Ticket $ticket): JsonResponse
    { // 关闭工单
        if ($ticket->close()) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.close')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.close')])]);
    }
}
