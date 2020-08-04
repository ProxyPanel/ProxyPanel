<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendVerifyCode extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $code; // 要发送的验证码

    public function __construct($id, $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function build()
    {
        return $this->view('emails.sendVerifyCode')->subject('发送注册验证码')->with([
            'code' => $this->code
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
