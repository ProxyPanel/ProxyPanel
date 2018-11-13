<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarning extends Mailable
{
    use Queueable, SerializesModels;

    protected $lastCanUseDays;

    public function __construct($lastCanUseDays)
    {
        $this->lastCanUseDays = $lastCanUseDays;
    }

    public function build()
    {
        return $this->view('emails.userExpireWarning')->subject('账号过期提醒')->with([
            'lastCanUseDays' => $this->lastCanUseDays
        ]);
    }
}
