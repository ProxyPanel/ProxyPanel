<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendUserInfo extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $content; // 账号信息

    public function __construct($id, $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.sendUserInfo')->subject('发送账号信息')->with([
            'content' => $this->content
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
