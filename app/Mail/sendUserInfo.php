<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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

    public function build(): sendUserInfo
    {
        return $this->view('emails.sendUserInfo')->subject('发送账号信息')->with(['content' => $this->content]);
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
