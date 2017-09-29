<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class userTrafficWarning extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $usedPercent;

    public function __construct($websiteName, $usedPercent)
    {
        $this->websiteName = $websiteName;
        $this->usedPercent = $usedPercent;
    }

    public function build()
    {
        return $this->view('emails.userTrafficWarning')->subject('流量警告')->with([
            'websiteName' => $this->websiteName,
            'usedPercent' => $this->usedPercent
        ]);
    }
}
