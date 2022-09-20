<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'               => 'required|string',
            'sn'                 => 'exclude_unless:type,3|unique:coupon',
            'logo'               => 'nullable|image',
            'type'               => 'required|numeric|between:1,3',
            'priority'           => 'nullable|numeric|min:0|max:255',
            'usable_times'       => 'nullable|numeric|min:1',
            'value'              => 'required|numeric|min:0',
            'minimum'            => 'nullable|numeric',
            'used'               => 'nullable|numeric',
            'levels'             => 'nullable|array',
            'groups'             => 'nullable|array',
            'users_whitelist'    => 'nullable|string',
            'users_blacklist'    => 'nullable|string',
            'services_blacklist' => 'nullable|string',
            'services_whitelist' => 'nullable|string',
            'coupon'             => 'nullable',
            'order'              => 'nullable',
            'days'               => 'nullable|numeric',
            'num'                => 'required|numeric|min:1',
            'start_time'         => 'required|date|before_or_equal:end_time',
            'end_time'           => 'required|date|after_or_equal:start_time',
        ];
    }
}
