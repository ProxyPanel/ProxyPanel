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
    protected $fillable = ['invitee_id', 'status'];

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
                $status_label = '<span class="badge badge-success">' . trans(
                        'home.invite_code_table_status_un'
                    ) . '</span>';
                break;
            case 1:
                $status_label = '<span class="badge badge-danger">' . trans(
                        'home.invite_code_table_status_yes'
                    ) . '</span>';
                break;
            case 2:
                $status_label = '<span class="badge badge-default">' . trans(
                        'home.invite_code_table_status_expire'
                    ) . '</span>';
                break;
            default:
                $status_label = '<span class="badge badge-default"> 未知 </span>';
        }

        return $status_label;
    }

}
