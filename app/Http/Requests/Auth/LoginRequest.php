<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => 'required|'.(sysConfig('username_type') ?? 'email'),
            'password' => 'required',
        ];
    }
}
