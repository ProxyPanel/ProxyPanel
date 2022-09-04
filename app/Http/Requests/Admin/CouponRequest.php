<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'               => 'required|string',
            'sn'                 => 'unique:coupon',
            'logo'               => 'nullable|image',
            'type'               => 'required|numeric|between:1,3',
            'usable_times'       => 'nullable|numeric',
            'value'              => 'required|numeric|min:0',
            'rule'               => 'nullable|numeric',
            'num'                => 'required|numeric|min:1',
            'start_time'         => 'required|date|before_or_equal:end_time',
            'end_time'           => 'required|date|after_or_equal:start_time',
        ];
    }
}
