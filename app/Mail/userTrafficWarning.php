<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class userTrafficWarning extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $id; // 邮件记录ID
    protected $usedPercent; // 已使用百分比

    public function __construct($id, $usedPercent)
    {
        $this->id = $id;
        $this->usedPercent = $usedPercent;
    }

    public function build(): userTrafficWarning
    {
        return $this->view('emails.userTrafficWarning')->subject('流量警告')->with(['usedPercent' => $this->usedPercent]);
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
