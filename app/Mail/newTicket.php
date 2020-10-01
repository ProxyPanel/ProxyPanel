<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class newTicket extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $id; // 邮件记录ID
    protected $title; // 工单标题
    protected $content; // 工单内容

    public function __construct($id, $title, $content)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
    }

    public function build(): newTicket
    {
        return $this->view('emails.newTicket')->subject('新工单提醒')->with([
            'title'   => $this->title,
            'content' => $this->content,
        ]);
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
