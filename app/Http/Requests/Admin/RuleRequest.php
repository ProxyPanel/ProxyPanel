<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'required|numeric|between:1,4',
            'name' => 'required|string',
            'pattern' => 'required|string',
        ];
    }
}
