<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarningToday extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;

    public function __construct($websiteName)
    {
        $this->websiteName = $websiteName;
    }

    public function build()
    {
        return $this->view('emails.userExpireWarningToday')->subject('账号过期提醒')->with([
            'websiteName'    => $this->websiteName,
        ]);
    }
}
