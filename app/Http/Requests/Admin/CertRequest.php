<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CertRequest extends FormRequest
{
    public function rules()
    {
        return [
            'domain' => 'required|string',
            'key' => 'string|nullable',
            'pem' => 'string|nullable',
        ];
    }
}
