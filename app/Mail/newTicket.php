<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class newTicket extends Mailable
{
    use Queueable, SerializesModels;

    protected $websiteName;
    protected $title;
    protected $content;

    public function __construct($websiteName, $title, $content)
    {
        $this->websiteName = $websiteName;
        $this->title = $title;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.newTicket')->subject('新工单提醒')->with([
            'websiteName' => $this->websiteName,
            'title'       => $this->title,
            'content'     => $this->content
        ]);
    }
}
