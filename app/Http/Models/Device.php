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

    function getTypeLabelAttribute()
    {
        switch ($this->attributes['type']) {
            case 1:
                $type_label = '<span class="label label-danger"> Shadowsocks(R) </span>';
                break;
            case 2:
                $type_label = '<span class="label label-danger"> V2Ray </span>';
                break;
            default:
                $type_label = '<span class="label label-default"> 其他 </span>';
        }

        return $type_label;
    }

    function getPlatformLabelAttribute()
    {
        switch ($this->attributes['platform']) {
            case 1:
                $platform_label = '<i class="fa fa-apple"></i> iOS';
                break;
            case 2:
                $platform_label = '<i class="fa fa-android"></i> Android';
                break;
            case 3:
                $platform_label = '<i class="fa fa-apple"></i> Mac';
                break;
            case 4:
                $platform_label = '<i class="fa fa-windows"></i> Windows';
                break;
            case 5:
                $platform_label = '<i class="fa fa-linux"></i> Linux';
                break;
            case 0:
            default:
                $platform_label = '其他';
        }

        return $platform_label;
    }
}