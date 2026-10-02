<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 数据库索引结构审计。
 *
 * 三个检查项：
 *  - redundant：重复索引（列与顺序完全相同）与左前缀冗余（一个索引的列是另一个索引的前缀）
 *  - unused   ：自 MySQL 启动以来一次都没被用到的索引
 *  - noindex  ：实际执行过、但没有走索引的语句
 *
 * 默认在线读取 information_schema / performance_schema（后两项需要 performance_schema 开启）；
 * --dump / --file= 改为离线分析 mysqldump 快照，只做 redundant，因为另外两项依赖运行期统计。
 */
class DbIndexAudit extends Command
{
    protected $signature = 'db:index-audit
                            {--dump : 离线分析 database/schema/mysql-schema.sql}
                            {--file= : 离线分析指定的 SQL 快照文件（优先于 --dump）}
                            {--only= : 只跑其中一项：redundant / unused / noindex}';

    protected $description = '索引结构审计：重复索引、未使用索引、未走索引的语句';

    public function handle(): int
    {
        $only = $this->option('only');

        if ($only && ! in_array($only, ['redundant', 'unused', 'noindex'], true)) {
            $this->error("--only 只支持 redundant / unused / noindex，收到：{$only}");

            return self::FAILURE;
        }

        $file = $this->option('file') ?: ($this->option('dump') ? database_path('schema/mysql-schema.sql') : null);

        try {
            if ($file) {
                $this->auditDump($file, $only);
            } else {
                $this->auditDatabase($only);
            }
        } catch (Exception $e) {
            $this->error('审计失败：'.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    // ------------------------------------------------------------ 两种数据来源

    private function auditDump(string $file, ?string $only): void
    {
        if (! is_file($file)) {
            throw new Exception("快照文件不存在：{$file}");
        }

        $structure = $this->parseDump(file_get_contents($file));
        $this->line("离线分析 {$file}：".count($structure['indexes']).' 个索引、'.count($structure['foreignKeys']).' 个外键');
        $this->reportRedundant($structure['indexes'], $structure['foreignKeys']);

        if ($only !== 'redundant') {
            $this->newLine();
            $this->warn('「未使用索引」与「未走索引的语句」依赖 performance_schema 的运行期统计，离线模式无法提供。');
        }
    }

    private function auditDatabase(?string $only): void
    {
        $database = DB::getDatabaseName();
        $this->line("在线审计数据库 {$database}");

        $indexes = $this->loadIndexes($database);
        $foreignKeys = $this->loadForeignKeys($database);
        $this->line('共 '.count($indexes).' 个索引、'.count($foreignKeys).' 个外键、'
            .count(array_unique(array_column($indexes, 'table'))).' 张表');

        if (! $only || $only === 'redundant') {
            $this->reportRedundant($indexes, $foreignKeys);
        }

        if (! $only || $only === 'unused') {
            $this->reportUnused($database, $indexes, $foreignKeys);
        }

        if (! $only || $only === 'noindex') {
            $this->reportNoIndex($database);
        }
    }

    /** 从 information_schema 读全部索引，按「表 + 索引名」聚合成一条一条的索引 */
    private function loadIndexes(string $database): array
    {
        $rows = DB::select(
            'select TABLE_NAME as tbl, INDEX_NAME as idx, NON_UNIQUE as non_unique, COLUMN_NAME as col, SUB_PART as sub_part
             from information_schema.STATISTICS
             where TABLE_SCHEMA = ?
             order by TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX',
            [$database]
        );

        $indexes = [];
        foreach ($rows as $row) {
            $key = $row->tbl.'.'.$row->idx;
            $indexes[$key] ??= ['table' => $row->tbl, 'name' => $row->idx, 'unique' => ! $row->non_unique, 'columns' => []];
            $indexes[$key]['columns'][] = $row->sub_part ? $row->col.'('.$row->sub_part.')' : $row->col;
        }

        return array_values($indexes);
    }

    /** 读外键的列（外键必须有一个以它开头的索引，这是判断索引能否删除的依据） */
    private function loadForeignKeys(string $database): array
    {
        $rows = DB::select(
            'select TABLE_NAME as tbl, CONSTRAINT_NAME as fk, COLUMN_NAME as col, ORDINAL_POSITION as seq
             from information_schema.KEY_COLUMN_USAGE
             where TABLE_SCHEMA = ? and REFERENCED_TABLE_NAME is not null
             order by TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION',
            [$database]
        );

        $foreignKeys = [];
        foreach ($rows as $row) {
            $key = $row->tbl.'.'.$row->fk;
            $foreignKeys[$key] ??= ['table' => $row->tbl, 'name' => $row->fk, 'columns' => []];
            $foreignKeys[$key]['columns'][] = $row->col;
        }

        return array_values($foreignKeys);
    }

    /** 解析 mysqldump 快照里的 CREATE TABLE，取出索引与外键 */
    private function parseDump(string $sql): array
    {
        $indexes = [];
        $foreignKeys = [];

        preg_match_all('/^CREATE TABLE `([a-z_0-9]+)` \((.*?)^\) ENGINE=/ms', $sql, $tables, PREG_SET_ORDER);

        foreach ($tables as $table) {
            foreach (explode("\n", $table[2]) as $line) {
                if (preg_match('/^\s*(PRIMARY KEY|UNIQUE KEY|KEY)\s+(?:`([^`]+)`\s+)?\(([^)]*)\)/', $line, $key)) {
                    $indexes[] = [
                        'table' => $table[1],
                        'name' => $key[1] === 'PRIMARY KEY' ? 'PRIMARY' : $key[2],
                        'unique' => $key[1] !== 'KEY',
                        'columns' => $this->splitColumns($key[3]),
                    ];
                } elseif (preg_match('/^\s*CONSTRAINT `([^`]+)` FOREIGN KEY \(([^)]*)\)/', $line, $fk)) {
                    $foreignKeys[] = ['table' => $table[1], 'name' => $fk[1], 'columns' => $this->splitColumns($fk[2])];
                }
            }
        }

        return ['indexes' => $indexes, 'foreignKeys' => $foreignKeys];
    }

    private function splitColumns(string $list): array
    {
        return array_values(array_filter(array_map(
            static fn ($column) => str_replace(['`', ' '], '', $column),
            explode(',', $list)
        )));
    }

    // ------------------------------------------------------------ 检查一：重复与左前缀冗余

    /** @return array<array{table: string, name: string, columns: array, reason: string, blocker: ?string}> */
    private function findRedundant(array $indexes, array $foreignKeys): array
    {
        $byTable = [];
        foreach ($indexes as $index) {
            $byTable[$index['table']][] = $index;
        }

        $findings = [];
        foreach ($byTable as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($index['name'] === 'PRIMARY') {
                    continue; // 主键不能删，但可以作为覆盖者让别人冗余
                }

                $reason = null;
                foreach ($tableIndexes as $other) {
                    if ($other['name'] === $index['name'] || $other['name'] === 'PRIMARY') {
                        continue;
                    }

                    if ($index['columns'] === $other['columns']) {
                        // 列完全相同：只有在对方更该保留时才判本索引冗余（唯一优先、其次名字靠前），
                        // 否则两个互为副本的索引会互相判删
                        if ([$other['unique'] ? 0 : 1, $other['name']] >= [$index['unique'] ? 0 : 1, $index['name']]) {
                            continue;
                        }

                        $reason = "列与顺序和 {$other['name']} 完全相同";
                        break;
                    }

                    // 前缀冗余：本索引的列是别人索引的前缀，别人能顶替它的所有查询。
                    // 唯一索引不走这条：唯一性约束只有它自己有，删了就没了。
                    if (! $index['unique']
                        && count($index['columns']) < count($other['columns'])
                        && $this->isPrefixOf($index['columns'], $other['columns'])) {
                        $reason = "列是 {$other['name']}(".implode(',', $other['columns']).') 的前缀';
                        break;
                    }
                }

                if ($reason !== null) {
                    $findings[] = [
                        'table' => $table,
                        'name' => $index['name'],
                        'columns' => $index['columns'],
                        'reason' => $reason,
                        'blocker' => $this->foreignKeyBlocker($table, $index, $tableIndexes, $foreignKeys),
                    ];
                }
            }
        }

        return $findings;
    }

    /** 该索引是否是某个外键唯一可用的索引；是则返回阻止删除的原因 */
    private function foreignKeyBlocker(string $table, array $index, array $tableIndexes, array $foreignKeys): ?string
    {
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey['table'] !== $table || ! $this->servesForeignKey($index['columns'], $foreignKey['columns'])) {
                continue;
            }

            foreach ($tableIndexes as $candidate) {
                if ($candidate['name'] !== $index['name'] && $this->servesForeignKey($candidate['columns'], $foreignKey['columns'])) {
                    continue 2; // 还有别的索引能服务这个外键
                }
            }

            return "外键 {$foreignKey['name']} 只有它能提供索引";
        }

        return null;
    }

    /** 索引的列以目标列为前缀（列出 a,b,c 的索引可以顶替只要 a 的查询） */
    private function isPrefixOf(array $columns, array $target): bool
    {
        return $columns === array_slice($target, 0, count($columns));
    }

    /**
     * 索引能否服务外键：MySQL 只要求索引的**最左若干列**与外键列相同，
     * 所以这里判断的是「外键列是索引列的前缀」，方向与 isPrefixOf 相反。
     */
    private function servesForeignKey(array $indexColumns, array $foreignKeyColumns): bool
    {
        return array_slice($indexColumns, 0, count($foreignKeyColumns)) === array_values($foreignKeyColumns);
    }

    private function reportRedundant(array $indexes, array $foreignKeys): void
    {
        $findings = $this->findRedundant($indexes, $foreignKeys);

        $this->newLine();
        $this->line('一、重复 / 左前缀冗余索引');

        if (! $findings) {
            $this->info('  没有发现重复或前缀冗余的索引。');

            return;
        }

        $rows = [];
        foreach ($findings as $finding) {
            $rows[] = [
                $finding['table'],
                $finding['name'],
                implode(', ', $finding['columns']),
                $finding['reason'],
                $finding['blocker']
                    ? '保留：'.$finding['blocker']
                    : "drop index `{$finding['name']}` on `{$finding['table']}`;",
            ];
        }

        $this->table(['表', '索引', '列', '判据', '处置'], $rows);

        $droppable = count(array_filter($findings, static fn ($f) => $f['blocker'] === null));
        $this->line("  共 {$droppable} 个可直接删除（删除前请确认没有手工 SQL 或运维脚本依赖）。");
    }

    // ------------------------------------------------------------ 检查二：未被使用的索引

    private function reportUnused(string $database, array $indexes, array $foreignKeys): void
    {
        $this->newLine();
        $this->line('二、自 MySQL 启动以来未被使用的索引');

        try {
            $rows = DB::select(
                'select OBJECT_NAME as tbl, INDEX_NAME as idx
                 from performance_schema.table_io_waits_summary_by_index_usage
                 where OBJECT_SCHEMA = ? and INDEX_NAME is not null and COUNT_STAR = 0
                 order by OBJECT_NAME, INDEX_NAME',
                [$database]
            );
        } catch (Exception $e) {
            $this->warn('  读不到 performance_schema（未开启或权限不足），跳过：'.$e->getMessage());

            return;
        }

        $uptime = $this->mysqlUptime();
        if ($uptime !== null) {
            $this->line('  统计窗口：MySQL 已运行 '.round($uptime / 86400, 1).' 天'
                .'（若期间执行过 flush 统计或重启，窗口会更短，结论需相应打折）');
        }

        $known = [];
        foreach ($indexes as $index) {
            $known[$index['table'].'.'.$index['name']] = $index;
        }

        $table = [];
        foreach ($rows as $row) {
            if ($row->idx === 'PRIMARY') {
                continue; // 主键无法删除，未使用也不构成待办
            }

            $index = $known[$row->tbl.'.'.$row->idx] ?? ['table' => $row->tbl, 'name' => $row->idx, 'columns' => []];
            $blocker = $this->foreignKeyBlocker($row->tbl, $index, $indexes, $foreignKeys);

            $table[] = [
                $row->tbl,
                $row->idx,
                implode(', ', $index['columns']),
                $blocker ? '保留：'.$blocker : "drop index `{$row->idx}` on `{$row->tbl}`;",
            ];
        }

        if (! $table) {
            $this->info('  没有发现从未被使用的索引。');

            return;
        }

        $this->table(['表', '索引', '列', '处置'], $table);
        $this->line('  提示：先用 --dump 复核重复索引，再考虑删除这里列出的索引。');
    }

    private function mysqlUptime(): ?int
    {
        try {
            $row = DB::selectOne("show global status like 'Uptime'");

            return $row ? (int) $row->Value : null;
        } catch (Exception $e) {
            return null;
        }
    }

    // ------------------------------------------------------------ 检查三：没走索引的语句

    private function reportNoIndex(string $database): void
    {
        $this->newLine();
        $this->line('三、执行过但没有走索引的语句（按扫描行数排序）');

        try {
            $rows = DB::select(
                'select DIGEST_TEXT as digest, COUNT_STAR as calls, SUM_NO_INDEX_USED as no_index,
                        SUM_ROWS_EXAMINED as rows_examined, SUM_TIMER_WAIT as total_wait
                 from performance_schema.events_statements_summary_by_digest
                 where SCHEMA_NAME = ? and SUM_NO_INDEX_USED > 0 and DIGEST_TEXT is not null
                 order by SUM_ROWS_EXAMINED desc
                 limit 20',
                [$database]
            );
        } catch (Exception $e) {
            $this->warn('  读不到 performance_schema（未开启或权限不足），跳过：'.$e->getMessage());

            return;
        }

        if (! $rows) {
            $this->info('  没有记录到未走索引的语句。');

            return;
        }

        $table = [];
        foreach ($rows as $row) {
            $table[] = [
                $row->calls,
                $row->no_index,
                number_format((float) $row->rows_examined),
                round(((float) $row->total_wait) / 1e9).' ms',
                Str::limit(preg_replace('/\s+/', ' ', (string) $row->digest), 80),
            ];
        }

        $this->table(['执行次数', '未走索引', '扫描行数', '总耗时', '语句摘要'], $table);
        $this->line('  提示：对高扫描行数的语句跑 EXPLAIN，确认是缺索引还是索引失效（列被函数包裹、隐式类型转换等）。');
        $this->line('  注意：该统计表只保留占用最高的前若干条摘要，重启或 truncate 后清零，默认上限见 performance_schema_digests_size。');
    }
}
