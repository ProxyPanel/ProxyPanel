<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserGroupRequest extends FormRequest
{
    public function rules(): array
    {
        $unq_name = '';
        if (in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $unq_name = ','.$this->group->id;
        }

        return [
            'name' => 'required|string|unique:user_group,name'.$unq_name,
            'nodes' => 'nullable|exists:node,id',
        ];
    }
}
