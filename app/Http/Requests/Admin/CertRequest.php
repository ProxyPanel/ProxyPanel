<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CertRequest extends FormRequest
{
    public function rules()
    {
        $unq_domain = '';
        if ($this->method() === 'PUT' || $this->method() === 'PATCH') {
            $unq_domain = ','.$this->cert->id;
        }

        return [
            'domain' => 'required|string|unique:node_certificate,domain'.$unq_domain,
            'key' => 'string|nullable',
            'pem' => 'string|nullable',
        ];
    }
}
