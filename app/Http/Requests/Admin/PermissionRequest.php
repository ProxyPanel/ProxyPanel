<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    public function rules(): array
    {
        $unq_name = '';
        if (in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $unq_name = ','.$this->permission->id;
        }

        return [
            'name' => 'required|string|unique:permissions,name'.$unq_name,
            'description' => 'required|string',
        ];
    }
}
