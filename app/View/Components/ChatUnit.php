<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ChatUnit extends Component
{
    public $user;

    public $ticket;

    public function __construct($user, $ticket)
    {
        $this->user = $user;
        $this->ticket = $ticket;
    }

    public function render()
    {
        return view('components.chat-unit');
    }
}
