<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarningToday extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function build()
    {
        return $this->view('emails.userExpireWarningToday')->subject('账号过期提醒');
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
