<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendUserInfo extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $content;

    public function __construct($websiteName, $content)
    {
        $this->websiteName = $websiteName;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.sendUserInfo')->subject('发送账号信息')->with([
            'websiteName' => $this->websiteName,
            'content'     => $this->content
        ]);
    }
}
