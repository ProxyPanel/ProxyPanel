<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'level' => 'required|exists:level,level',
            'renew' => 'required_unless:type,2|numeric|min:0|nullable',
            'period' => 'required_unless:type,2|numeric|min:0|nullable',
            'invite_num' => 'numeric',
            'limit_num' => 'numeric',
            'is_hot' => 'nullable|string',
            'status' => 'nullable|string',
            'sort' => 'numeric',
            'speed_limit' => 'numeric',
            'color' => 'nullable|string',
            'logo' => 'nullable|image',
            'description' => 'nullable|string',
            'info' => 'nullable|string',
        ];
    }
}
