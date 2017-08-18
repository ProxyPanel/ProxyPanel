<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class resetPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $resetPasswordUrl;

    public function __construct($websiteName, $resetPasswordUrl)
    {
        $this->websiteName = $websiteName;
        $this->resetPasswordUrl = $resetPasswordUrl;
    }

    public function build()
    {
        return $this->view('emails.resetPassword')->subject('重置密码')->with([
            'websiteName'      => $this->websiteName,
            'resetPasswordUrl' => $this->resetPasswordUrl
        ]);
    }
}
