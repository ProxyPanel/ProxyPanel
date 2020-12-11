<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'renew' => 'required_unless:type,2|min:0',
            'logo' => 'nullable|image',
        ];
    }
}
