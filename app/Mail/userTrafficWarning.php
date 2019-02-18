<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userTrafficWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $usedPercent; // 已使用百分比

    public function __construct($id, $usedPercent)
    {
        $this->id = $id;
        $this->usedPercent = $usedPercent;
    }

    public function build()
    {
        return $this->view('emails.userTrafficWarning')->subject('流量警告')->with([
            'usedPercent' => $this->usedPercent
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
