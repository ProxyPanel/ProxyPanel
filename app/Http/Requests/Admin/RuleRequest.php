<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string',
            'pattern' => 'required|string',
        ];

        if (! in_array($this->method(), ['PATCH', 'PUT'], true)) {
            $rules = array_merge(['type' => 'required|numeric|between:1,4'], $rules);
        }

        return $rules;
    }
}
