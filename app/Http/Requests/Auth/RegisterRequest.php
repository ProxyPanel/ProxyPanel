<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => 'required',
            'email' => 'required|email|unique:user',
            'password' => 'required|min:6|confirmed',
            'term' => 'accepted',
        ];
    }
}
