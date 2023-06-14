<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function rules(): array
    {
        $unq_name = '';
        if (in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $unq_name = ','.$this->role->id;
        }

        return [
            'name' => 'required|string|unique:roles,name'.$unq_name,
            'description' => 'required|string',
            'permissions' => 'nullable|exists:permissions,name',
        ];
    }
}
