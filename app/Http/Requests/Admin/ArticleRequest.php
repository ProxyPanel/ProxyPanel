<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'   => 'required',
            'type'    => 'required|numeric',
            'summary' => 'nullable',
            'logo'    => 'nullable|exclude_if:type,4|image',
            'content' => 'required',
            'sort'    => 'required_if:type,1|numeric',
        ];
    }
}
