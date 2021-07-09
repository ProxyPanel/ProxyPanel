<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nickname' => 'required',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|unique:user,username',
            'password' => 'required|min:6|confirmed',
            'term'     => 'accepted',
        ];
    }
}
