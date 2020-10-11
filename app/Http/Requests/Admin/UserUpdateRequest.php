<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username'        => 'required',
            'email'           => 'required|unique:user,email,'.$this->user,
            'port'            => 'required|numeric|exclude_if:port,0|gt:0|unique:user,port,'.$this->user,
            'passwd'          => 'required|string',
            'uuid'            => 'required|uuid',
            'transfer_enable' => 'required|numeric',
            'enable'          => 'required|boolean',
            'method'          => 'required|string',
            'protocol'        => 'required|string',
            'obfs'            => 'required|string',
            'speed_limit'     => 'required|numeric',
            'expired_at'      => 'required|date_format:Y-m-d',
            'remark'          => 'nullable|string',
            'level'           => 'required|numeric',
            'group_id'        => 'numeric',
            'is_admin'        => 'boolean|exclude_unless:id,1|gte:1',
            'reset_time'      => 'nullable|date_format:Y-m-d',
            'invite_num'      => 'numeric',
            'status'          => 'required|integer|between:-1,1',
        ];
    }
}
