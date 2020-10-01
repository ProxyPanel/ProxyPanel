<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'         => 'required',
            'sn'           => 'unique:coupon',
            'type'         => 'required|integer|between:1,3',
            'usable_times' => 'integer|nullable',
            'num'          => 'required|integer|min:1',
            'value'        => 'required|numeric|min:0',
            'start_time'   => 'required|date|before_or_equal:end_time',
            'end_time'     => 'required|date|after_or_equal:start_time',
        ];
    }
}
