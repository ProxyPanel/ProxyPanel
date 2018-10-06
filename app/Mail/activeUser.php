<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class activeUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $activeUserUrl;

    public function __construct($websiteName, $activeUserUrl)
    {
        $this->websiteName = $websiteName;
        $this->activeUserUrl = $activeUserUrl;
    }

    public function build()
    {
        return $this->view('emails.activeUser')->subject('激活账号')->with([
            'websiteName'   => $this->websiteName,
            'activeUserUrl' => $this->activeUserUrl
        ]);
    }
}
