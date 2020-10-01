<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'    => 'required',
            'traffic' => 'required|integer|min:1|max:10240000|nullable',
            'price'   => 'required|numeric|min:0',
            'type'    => 'required',
            'renew'   => 'required_unless:type,2|min:0',
            'days'    => 'required|integer',
        ];
    }
}
