<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|'.(sysConfig('username_type') ?? 'email'),
            'password' => 'required',
        ];
    }
}
