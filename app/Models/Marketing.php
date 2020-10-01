<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 营销
 */
class Marketing extends Model
{
    protected $table = 'marketing';

    public function getStatusLabelAttribute(): string
    {
        $status_label = '';
        switch ($this->attributes['status']) {
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
