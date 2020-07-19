<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 审计规则
 *
 * @property int         $id
 * @property int         $type    类型：1-正则表达式、2-域名、3-IP、4-协议
 * @property string      $name    规则描述
 * @property string      $pattern 规则值
 * @property-read string $type_api_label
 * @property-read string $type_label
 * @method static Builder|Rule newModelQuery()
 * @method static Builder|Rule newQuery()
 * @method static Builder|Rule query()
 * @method static Builder|Rule whereId($value)
 * @method static Builder|Rule whereName($value)
 * @method static Builder|Rule wherePattern($value)
 * @method static Builder|Rule whereType($value)
 * @mixin \Eloquent
 */
class Rule extends Model {
	protected $table = 'rule';

	public function getTypeLabelAttribute(): string {
		switch($this->attributes['type']){
			case 1:
				$type_label = '正则表达式';
				break;
			case 2:
				$type_label = '域 名';
				break;
			case 3:
				$type_label = 'I P';
				break;
			case 4:
				$type_label = '协 议';
				break;
			default:
				$type_label = '未 知';

		}
		return $type_label;
	}

	public function getTypeApiLabelAttribute(): string {
		switch($this->attributes['type']){
			case 1:
				$type_api_label = 'reg';
				break;
			case 2:
				$type_api_label = 'domain';
				break;
			case 3:
				$type_api_label = 'ip';
				break;
			case 4:
				$type_api_label = 'protocol';
				break;
			default:
				$type_api_label = 'unknown';

		}
		return $type_api_label;
	}
}
