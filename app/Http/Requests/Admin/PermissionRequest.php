<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
        ];
    }
}
