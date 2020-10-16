<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendVerifyCode extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $id; // 邮件记录ID
    protected $code; // 要发送的验证码

    public function __construct($id, $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function build(): sendVerifyCode
    {
        return $this->view('emails.sendVerifyCode')->subject('发送注册验证码')->with(['code' => $this->code]);
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
