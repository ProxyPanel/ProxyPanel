<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nickname' => 'required',
            'username' => 'required|unique:user,username,'.$this->user,
            'password' => 'nullable|string|nullable',
            'port' => 'nullable|numeric',
            'passwd' => 'nullable|string',
            'uuid' => 'nullable|uuid',
            'transfer_enable' => 'required|numeric|min:0',
            'enable' => 'required|boolean',
            'method' => 'required|string|exists:ss_config,name',
            'protocol' => 'required|string|exists:ss_config,name',
            'obfs' => 'required|string|exists:ss_config,name',
            'speed_limit' => 'required|numeric|min:0',
            'wechat' => 'nullable|string',
            'qq' => 'nullable|string',
            'expired_at' => 'nullable|date_format:Y-m-d',
            'remark' => 'nullable|string',
            'level' => 'required|exists:level,level',
            'user_group_id' => 'nullable|exists:user_group,id',
            'roles' => 'nullable|exists:roles,name',
            'reset_time' => 'nullable|date_format:Y-m-d',
            'invite_num' => 'required|numeric|min:0',
            'status' => 'required|numeric|between:-1,1',
        ];
    }
}
