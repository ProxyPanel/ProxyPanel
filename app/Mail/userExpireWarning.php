<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $lastCanUseDays; // 剩余可用天数

    public function __construct($id, $lastCanUseDays)
    {
        $this->id = $id;
        $this->lastCanUseDays = $lastCanUseDays;
    }

    public function build()
    {
        return $this->view('emails.userExpireWarning')->subject('账号过期提醒')->with([
            'lastCanUseDays' => $this->lastCanUseDays
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
