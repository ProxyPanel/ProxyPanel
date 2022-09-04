<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RbacRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'        => 'required|string',
            'description' => 'required|string',
        ];
    }
}
