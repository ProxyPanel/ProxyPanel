<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserGroupRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'nodes' => 'exists:node,id',
        ];
    }
}
