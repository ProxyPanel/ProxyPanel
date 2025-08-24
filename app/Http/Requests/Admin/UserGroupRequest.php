<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:user_group,name',
            'nodes' => 'nullable|exists:node,id',
        ];
    }
}
