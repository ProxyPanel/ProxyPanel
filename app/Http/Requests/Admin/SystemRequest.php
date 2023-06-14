<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SystemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|exists:config,name',
            'value' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.exists' => '设置项目不存在于数据库',
        ];
    }
}
