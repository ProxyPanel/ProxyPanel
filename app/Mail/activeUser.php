<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class activeUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $activeUserUrl; // 激活用户URL

    public function __construct($id, $activeUserUrl)
    {
        $this->id = $id;
        $this->activeUserUrl = $activeUserUrl;
    }

    public function build()
    {
        return $this->view('emails.activeUser')->subject('激活账号')->with([
            'activeUserUrl' => $this->activeUserUrl
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
