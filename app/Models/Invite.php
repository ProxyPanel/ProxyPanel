<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 邀请码
 */
class Invite extends Model
{
    use SoftDeletes;

    protected $table = 'invite';
    protected $dates = ['dateline', 'deleted_at'];
    protected $guarded = [];

    public function scopeUid($query)
    {
        return $query->whereInviterId(Auth::id());
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case 0:
                $status_label = '<span class="badge badge-success">'.trans('common.status.unused').'</span>';
                break;
            case 1:
                $status_label = '<span class="badge badge-danger">'.trans('common.status.used').'</span>';
                break;
            case 2:
                $status_label = '<span class="badge badge-default">'.trans('common.status.expire').'</span>';
                break;
            default:
                $status_label = '<span class="badge badge-default"> '.trans('common.status.unknown').' </span>';
        }

        return $status_label;
    }
}
