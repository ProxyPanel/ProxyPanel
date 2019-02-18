<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class resetPassword extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $resetPasswordUrl; // 重置密码URL

    public function __construct($id, $resetPasswordUrl)
    {
        $this->id = $id;
        $this->resetPasswordUrl = $resetPasswordUrl;
    }

    public function build()
    {
        return $this->view('emails.resetPassword')->subject('重置密码')->with([
            'resetPasswordUrl' => $this->resetPasswordUrl
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
