<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class nodeCrashWarning extends Mailable
{
    use Queueable, SerializesModels;

    protected $nodeName;
    protected $nodeServer;

    public function __construct($nodeName, $nodeServer)
    {
        $this->nodeName = $nodeName;
        $this->nodeServer = $nodeServer;
    }

    public function build()
    {
        return $this->view('emails.nodeCrashWarning')->subject('节点宕机警告')->with([
            'nodeName'   => $this->nodeName,
            'nodeServer' => $this->nodeServer
        ]);
    }
}
