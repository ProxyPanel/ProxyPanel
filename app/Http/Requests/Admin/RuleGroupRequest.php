<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RuleGroupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'type' => 'required|boolean',
            'rules' => 'nullable|exists:rule,id',
        ];
    }
}
