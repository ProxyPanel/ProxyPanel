<?php

namespace App\Http\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 订阅设备列表
 * Class Device
 *
 * @package App\Http\Models
 * @mixin Eloquent
 * @property int        $id
 * @property int        $type     类型：0-兼容、1-Shadowsocks(R)、2-V2Ray
 * @property int        $platform 所属平台：0-其他、1-iOS、2-Android、3-Mac、4-Windows、5-Linux
 * @property string     $name     设备名称
 * @property int        $status   状态：0-禁止订阅、1-允许订阅
 * @property string     $header   请求时头部的识别特征码
 * @property-read mixed $platform_label
 * @property-read mixed $type_label
 * @method static Builder|Device newModelQuery()
 * @method static Builder|Device newQuery()
 * @method static Builder|Device query()
 * @method static Builder|Device whereHeader($value)
 * @method static Builder|Device whereId($value)
 * @method static Builder|Device whereName($value)
 * @method static Builder|Device wherePlatform($value)
 * @method static Builder|Device whereStatus($value)
 * @method static Builder|Device whereType($value)
 */
class Device extends Model
{
	public $timestamps = FALSE;
	protected $table = 'device';
	protected $primaryKey = 'id';

	function getTypeLabelAttribute()
	{
		switch($this->attributes['type']){
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
		switch($this->attributes['platform']){
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