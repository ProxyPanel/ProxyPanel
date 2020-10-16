<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class userExpireWarning extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $id; // 邮件记录ID
    protected $lastCanUseDays; // 剩余可用天数

    public function __construct($id, $lastCanUseDays)
    {
        $this->id = $id;
        $this->lastCanUseDays = $lastCanUseDays;
    }

    public function build(): userExpireWarning
    {
        return $this->view('emails.userExpireWarning')->subject('账号过期提醒')->with(['lastCanUseDays' => $this->lastCanUseDays]);
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
