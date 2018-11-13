<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userExpireWarningToday extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->view('emails.userExpireWarningToday')->subject('账号过期提醒');
    }
}
