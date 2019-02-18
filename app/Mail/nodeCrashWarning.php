<?php

namespace App\Mail;

use App\Http\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class nodeCrashWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $id; // 邮件记录ID
    protected $nodeName; // 节点名称
    protected $nodeServer; // 节点地址

    public function __construct($id, $nodeName, $nodeServer)
    {
        $this->id = $id;
        $this->nodeName = $nodeName;
        $this->nodeServer = $nodeServer;
    }

    public function build()
    {
        return $this->view('emails.nodeCrashWarning')->subject('节点离线警告')->with([
            'nodeName'   => $this->nodeName,
            'nodeServer' => $this->nodeServer
        ]);
    }

    // 发件失败处理
    public function failed(\Exception $e)
    {
        EmailLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
    }
}
