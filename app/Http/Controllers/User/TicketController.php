<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:300',
        ]);

        // 清理内容，防止恶意代码
        $title = $validatedData['title'];
        $content = substr(str_replace(['atob', 'eval'], '', clean($validatedData['content'])), 0, 300);

        $ticket = auth()->user()->tickets()->create(compact('title', 'content'));

        if ($ticket) {
            // 通知相关管理员
            return response()->json([
                'status' => 'success',
                'message' => trans('common.success_item', ['attribute' => trans('common.submit')]),
            ]);
        }

        return response()->json([
            'status' => 'fail',
            'message' => trans('common.failed_item', ['attribute' => trans('common.create')]),
        ]);
    }

    public function edit(Ticket $ticket): View
    { // 回复工单
        $replyList = $ticket->reply()
            ->with('ticket:id,status', 'admin:id,username,qq', 'user:id,username,qq')
            ->oldest()
            ->get();

        return view('user.replyTicket', compact('ticket', 'replyList'));
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        $validatedData = $request->validate([
            'content' => 'required|string|max:300',
        ]);

        // 清理内容，防止恶意代码
        $content = substr(str_replace(['atob', 'eval'], '', clean($validatedData['content'])), 0, 300);

        $reply = $ticket->reply()->create([
            'user_id' => auth()->id(),
            'content' => $content,
        ]);

        if ($reply) {
            return response()->json([
                'status' => 'success',
                'message' => trans('common.success_item', ['attribute' => trans('user.ticket.reply')]),
            ]);
        }

        return response()->json([
            'status' => 'fail',
            'message' => trans('common.failed_item', ['attribute' => trans('user.ticket.reply')]),
        ]);
    }

    public function close(Ticket $ticket): JsonResponse
    { // 关闭工单
        if ($ticket->close()) {
            return response()->json([
                'status' => 'success',
                'message' => trans('common.success_item', ['attribute' => trans('common.close')]),
            ]);
        }

        return response()->json([
            'status' => 'fail',
            'message' => trans('common.failed_item', ['attribute' => trans('common.close')]),
        ]);
    }
}
