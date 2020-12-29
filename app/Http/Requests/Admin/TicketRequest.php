<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function rules()
    {
        return [
            'id' => 'required_without:email|exists:user,id|numeric|nullable',
            'email' => 'required_without:id|exists:user,email||nullable',
            'title' => 'required|string',
            'content' => 'required|string',
        ];
    }
}
