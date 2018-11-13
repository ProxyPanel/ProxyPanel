<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class activeUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $activeUserUrl;

    public function __construct($activeUserUrl)
    {
        $this->activeUserUrl = $activeUserUrl;
    }

    public function build()
    {
        return $this->view('emails.activeUser')->subject('激活账号')->with([
            'activeUserUrl' => $this->activeUserUrl
        ]);
    }
}
