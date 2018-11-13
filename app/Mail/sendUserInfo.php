<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendUserInfo extends Mailable
{
    use Queueable, SerializesModels;

    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.sendUserInfo')->subject('发送账号信息')->with([
            'content' => $this->content
        ]);
    }
}
