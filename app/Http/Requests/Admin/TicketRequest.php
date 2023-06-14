<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'uid' => 'required_without:username|exists:user,id|numeric|nullable',
            'username' => 'required_without:uid|exists:user,username||nullable',
            'title' => 'required|string',
            'content' => 'required|string',
        ];
    }
}
