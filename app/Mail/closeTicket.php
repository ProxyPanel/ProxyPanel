<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class closeTicket extends Mailable
{
    use Queueable, SerializesModels;

    protected $title;
    protected $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('emails.closeTicket')->subject('工单关闭提醒')->with([
            'title'   => $this->title,
            'content' => $this->content
        ]);
    }
}
