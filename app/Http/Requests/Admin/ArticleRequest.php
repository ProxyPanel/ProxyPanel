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
            'language' => 'required|string',
            'category' => 'nullable|string',
            'sort' => 'nullable|numeric',
            'logo' => 'nullable|'.($this->hasFile('logo') ? 'image' : 'url'),
            'content' => 'required',
        ];
    }
}
