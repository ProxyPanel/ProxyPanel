<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Contracts\View\View;
use Illuminate\Mail\Markdown;

class MessageController extends Controller
{
    public function index(string $type, string $msgId): View
    {
        //if ($type === 'markdown') {
        $log = NotificationLog::whereMsgId($msgId)->latest()->firstOrFail();
        $title = $log->title;
        $content = Markdown::parse($log->content)->toHtml();

        return view('components.message', compact('title', 'content'));
        //}
    }
}
