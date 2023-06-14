<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'nodes' => 'nullable|exists:node,id',
        ];
    }
}
