<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * 用户流量记录.
 */
class UserDataFlowLog extends Model
{
    public $timestamps = false;

    protected $table = 'user_traffic_log';

    protected $guarded = [];

    // 关联账号
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 关联节点
    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    /** 窗口内是否还有原始流量日志：没有日志时聚合结果就是空集，统计任务也就没有可重建的行 */
    public static function hasLogsInWindow(int $start, int $end): bool
    {
        return static::query()->whereBetween('log_time', [$start, $end])->exists();
    }

    /**
     * 窗口内的上报按 (账号, 节点) 在库侧合并成一行，不做活跃过滤.
     *
     * 窗口必须由调用方切到足够小（按小时），让 log_time 的区间扫描保住：窗口接近整表体积时
     * MySQL 会改用打头列是 user_id / node_id 的索引来免临时表，再为 u / d 逐行回表，读多少行由整表决定。
     */
    public static function pairTotals(int $start, int $end): Collection
    {
        return static::query()
            ->whereBetween('log_time', [$start, $end])
            ->groupBy('user_id', 'node_id')
            ->get(['user_id', 'node_id', DB::raw('SUM(u) as u'), DB::raw('SUM(d) as d')]);
    }

    /**
     * 窗口内产生过上报的活跃账号 id，按 log_time 区间取一次：读多少行只取决于窗口大小。
     *
     * 按账号驱动（whereHas('dataFlowLogs')）时 MySQL 对相关子查询按 user_id 走 ref 访问，
     * log_time 退化成逐行过滤，每个账号都把自己的整段历史读一遍，代价随本表体积而非窗口增长。
     *
     * @return Collection<int, int>
     */
    public static function activeUserIds(int $start, int $end): Collection
    {
        $userIds = static::query()->whereBetween('log_time', [$start, $end])->groupBy('user_id')->pluck('user_id');

        return User::activeUser()->whereIn('id', $userIds)->orderBy('id')->pluck('id');
    }
}
