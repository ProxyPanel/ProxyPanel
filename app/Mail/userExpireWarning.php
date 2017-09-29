<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarning extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $lastCanUseDays;

    public function __construct($websiteName, $lastCanUseDays)
    {
        $this->websiteName = $websiteName;
        $this->lastCanUseDays = $lastCanUseDays;
    }

    public function build()
    {
        return $this->view('emails.userExpireWarning')->subject('账号过期提醒')->with([
            'websiteName'    => $this->websiteName,
            'lastCanUseDays' => $this->lastCanUseDays
        ]);
    }
}
