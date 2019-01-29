<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 订阅设备列表
 * Class Device
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Device extends Model
{
    protected $table = 'device';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function getTypeLabelAttribute()
    {
        switch ($this->attributes['type']) {
            case 1:
                $type_label = '<span class="label label-info"> Shadowsocks(R) </span>';
                break;
            case 2:
                $type_label = '<span class="label label-info"> V2Ray </span>';
                break;
            default:
                $type_label = '<span class="label label-info"> 其他 </span>';
        }

        return $type_label;
    }

    public function getPlatformLabelAttribute()
    {
        switch ($this->attributes['platform']) {
            case 1:
                $platform_label = '<i class="fa fa-apple"></i>';
                break;
            case 2:
                $platform_label = '<i class="fa fa-android"></i>';
                break;
            case 3:
                $platform_label = '<i class="fa fa-apple"></i>';
                break;
            case 4:
                $platform_label = '<i class="fa fa-windows"></i>';
                break;
            case 5:
                $platform_label = '<i class="fa fa-linux"></i>';
                break;
            case 0:
            default:
                $platform_label = '其他';
        }

        return $platform_label;
    }
}