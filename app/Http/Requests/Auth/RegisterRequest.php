<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nickname' => 'required|string',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|unique:user,username',
            'password' => 'required|string|min:6|confirmed',
            'term' => 'accepted',
        ];
    }
}
