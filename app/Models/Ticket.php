<?php

namespace App\Models;

use App\Observers\TicketObserver;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 工单.
 */
#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    protected $table = 'ticket';

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reply(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function close(): bool
    {
        $this->status = 2;

        return $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => '<span class="badge badge-lg badge-success">'.trans('common.status.pending').'</span>',
            1 => '<span class="badge badge-lg badge-danger">'.trans('common.status.reply').'</span>',
            2 => '<span class="badge badge-lg badge-default">'.trans('common.status.closed').'</span>',
            default => '<span class="badge badge-lg badge-default">'.trans('common.status.unknown').'</span>',
        };
    }

    public static function getAvgFirstResponseData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $subQuery = TicketReply::query()
            ->select('ticket_id')
            ->selectRaw('MIN(created_at) as first_reply_time')
            ->whereNotNull('admin_id')
            ->groupBy('ticket_id');

        $result = self::query()
            ->joinSub($subQuery, 'reply', 'ticket.id', '=', 'reply.ticket_id')
            ->where('ticket.created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_count, 
                COALESCE(AVG(TIMESTAMPDIFF(SECOND, ticket.created_at, reply.first_reply_time)), 0) as avg_seconds
            ')
            ->first();

        return [
            'count' => (int) ($result->total_count ?? 0),
            'avg_time' => (float) ($result->avg_seconds ?? 0),
        ];
    }

    public static function getTicketStatusStats(): array
    {
        $stats = self::query()->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as replied,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as closed
        ')->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'pending' => (int) ($stats->pending ?? 0),
            'replied' => (int) ($stats->replied ?? 0),
            'closed' => (int) ($stats->closed ?? 0),
        ];
    }

    public static function getTodayTicketCount(): int
    {
        return (int) self::query()
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    public static function getAvgCloseTime(int $days = 30): ?float
    {
        $startDate = Carbon::now()->subDays($days);

        $subQuery = TicketReply::query()
            ->select('ticket_id')
            ->selectRaw('MAX(created_at) as last_reply_time')
            ->whereNotNull('admin_id')
            ->groupBy('ticket_id');

        $result = self::query()
            ->joinSub($subQuery, 'reply', 'ticket.id', '=', 'reply.ticket_id')
            ->where('ticket.status', 2) // 已关闭的工单
            ->where('ticket.created_at', '>=', $startDate)
            ->selectRaw('
                COALESCE(AVG(TIMESTAMPDIFF(SECOND, ticket.created_at, reply.last_reply_time)), 0) as avg_seconds
            ')
            ->first();

        return (float) ($result->avg_seconds ?? 0);
    }
}
