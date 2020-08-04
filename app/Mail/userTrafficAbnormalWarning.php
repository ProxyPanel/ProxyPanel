<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userTrafficAbnormalWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $title; // 邮件标题
    protected $content; // 邮件内容

    public function __construct($id, $title, $content)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.userTrafficAbnormalWarning')->subject('流量异常警告')->with([
            'content' => $this->content
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
