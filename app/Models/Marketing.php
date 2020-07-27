<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 营销
 *
 * @property int                        $id
 * @property int                        $type     类型：1-邮件群发
 * @property string                     $receiver 接收者
 * @property string                     $title    标题
 * @property string                     $content  内容
 * @property string|null                $error    错误信息
 * @property int                        $status   状态：-1-失败、0-待发送、1-成功
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string                $status_label
 * @method static Builder|Marketing newModelQuery()
 * @method static Builder|Marketing newQuery()
 * @method static Builder|Marketing query()
 * @method static Builder|Marketing whereContent($value)
 * @method static Builder|Marketing whereCreatedAt($value)
 * @method static Builder|Marketing whereError($value)
 * @method static Builder|Marketing whereId($value)
 * @method static Builder|Marketing whereReceiver($value)
 * @method static Builder|Marketing whereStatus($value)
 * @method static Builder|Marketing whereTitle($value)
 * @method static Builder|Marketing whereType($value)
 * @method static Builder|Marketing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Marketing extends Model {
	protected $table = 'marketing';
	protected $appends = ['status_label'];

	public function getStatusLabelAttribute(): string {
		$status_label = '';
		switch($this->attributes['status']){
			case -1:
				$status_label = '失败';
				break;
			case 0:
				$status_label = '待推送';
				break;
			case 1:
				$status_label = '成功';
				break;
		}

		return $status_label;
	}
}
