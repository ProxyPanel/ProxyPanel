<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|numeric|between:1,2',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'level' => 'required|exists:level,level',
            'renew' => 'required_unless:type,2|numeric|min:0|nullable',
            'period' => 'required_unless:type,2|numeric|min:0|nullable',
            'traffic' => 'required|numeric|min:1|max:10240000',
            'traffic_unit' => 'nullable|numeric',
            'invite_num' => 'numeric',
            'limit_num' => 'numeric',
            'speed_limit' => 'numeric',
            'days' => 'required|numeric',
            'is_hot' => 'nullable|string',
            'status' => 'nullable|string',
            'sort' => 'numeric',
            'color' => 'nullable|string',
            'logo' => 'nullable|image',
            'description' => 'nullable|string',
            'info' => 'nullable|string',
        ];
    }
}
