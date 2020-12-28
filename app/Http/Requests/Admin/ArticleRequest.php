<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|numeric|between:1,4',
            'title' => 'required|string',
            'summary' => 'string|nullable',
            'sort' => 'required_if:type,1|numeric',
            'logo' => 'nullable|exclude_if:type,4|image',
            'content' => 'required|string',
        ];
    }
}
