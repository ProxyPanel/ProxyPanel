<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required',
            'email'    => 'required|unique:user,email,'.$this->user,
        ];
    }
}
