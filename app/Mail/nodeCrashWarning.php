<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class nodeCrashWarning extends Mailable implements ShouldQueue
{

    use Queueable;
    use SerializesModels;

    protected $id; // 邮件记录ID

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function build(): nodeCrashWarning
    {
        return $this->view('emails.nodeCrashWarning')->subject('节点阻断警告')->with(
            [
                'content' => NotificationLog::find($this->id)->content,
            ]
        );
    }

    // 发件失败处理
    public function failed(Exception $e): void
    {
        NotificationLog::whereId($this->id)->update(
            ['status' => -1, 'error' => $e->getMessage()]
        );
    }

}
