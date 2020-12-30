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
            'password_confirmation' => 'required|same:password',
            'term' => 'accepted',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => trans('auth.email_null'),
            'email.required' => trans('auth.email_null'),
            'email.email' => trans('auth.email_legitimate'),
            'email.unique' => trans('auth.email_exist'),
            'password.required' => trans('auth.password_null'),
            'password.min' => trans('auth.password_limit'),
            'password_confirmation.required' => trans('auth.confirm_password'),
            'password_confirmation.same' => trans('auth.password_same'),
            'term.accepted' => trans('auth.unaccepted'),
        ];
    }
}
