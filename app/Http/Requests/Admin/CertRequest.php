<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CertRequest extends FormRequest
{
    public function rules(): array
    {
        $unq_domain = '';
        if (in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $unq_domain = ','.$this->cert->id;
        }

        return [
            'domain' => 'required|string|unique:node_certificate,domain'.$unq_domain,
            'key' => 'nullable|string',
            'pem' => 'nullable|string',
        ];
    }
}
