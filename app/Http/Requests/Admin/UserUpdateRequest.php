<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nickname' => 'required|string',
            'username' => 'required|'.(sysConfig('username_type') ?? 'email').'|unique:user,username,'.$this->user->id,
            'password' => 'nullable|string',
            'port' => 'required|numeric|exclude_if:port,0|gt:0|unique:user,port,'.$this->user->id,
            'passwd' => 'required|string',
            'vmess_id' => 'nullable|uuid',
            'transfer_enable' => 'required|numeric|min:0',
            'enable' => 'required|boolean',
            'method' => 'required|exists:ss_config,name',
            'protocol' => 'required|exists:ss_config,name',
            'obfs' => 'required|exists:ss_config,name',
            'speed_limit' => 'required|numeric|min:0',
            'wechat' => 'nullable|string',
            'qq' => 'nullable|string',
            'expired_at' => 'required|date_format:Y-m-d',
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
