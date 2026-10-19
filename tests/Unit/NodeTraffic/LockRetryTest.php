<?php

namespace Tests\Unit\NodeTraffic;

use App\Utils\NodeTraffic\LockRetry;
use Illuminate\Database\QueryException;
use PDOException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * 这里继承裸 PHPUnit 的 TestCase：LockRetry 是纯判定，不碰数据库也不启动容器。
 *
 * 钉住的判据是「只有死锁整笔回滚才重跑」：1205 与外键冲突必须在第一次就原样抛出。
 * 多跑一轮救不回这类报错，代价却不只是时间——重试期间 (node, report_id) 认领键一直被本次请求占着，
 * 节点超时后的整批重投会因认领键被当成已入账而当场丢弃，最终写入又失败就是永久丢账。
 */
class LockRetryTest extends TestCase
{
    private const MYSQL_DEADLOCK = 'SQLSTATE[HY000]: 1213 Deadlock found when trying to get lock; try restarting transaction';

    private const POSTGRES_DEADLOCK = 'SQLSTATE[40P01]: Deadlock: 1205 deadlock detected, please try running transaction again';

    private const SQL_SERVER_DEADLOCK = 'SQLSTATE[40001]: Serialization error: 1205 Transaction (Process ID 64) was deadlocked on lock resources with another process and has been chosen as the deadlock victim. Rerun the transaction.';

    private const LOCK_WAIT_TIMEOUT = 'SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction';

    private const FOREIGN_KEY = 'SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails';

    public function test_it_returns_one_when_the_write_succeeds_first_time(): void
    {
        $executions = 0;

        $result = LockRetry::run(function () use (&$executions) {
            $executions++;
        });

        $this->assertSame(1, $result);
        $this->assertSame(1, $executions);
    }

    public function test_it_retries_a_deadlock_until_the_write_succeeds(): void
    {
        $executions = 0;

        $result = LockRetry::run(function () use (&$executions) {
            $executions++;

            if ($executions < 3) {
                throw $this->deadlock();
            }
        });

        $this->assertSame(3, $result);
        $this->assertSame(3, $executions);
    }

    public function test_it_throws_the_last_exception_once_attempts_are_used_up(): void
    {
        $executions = 0;

        try {
            LockRetry::run(function () use (&$executions) {
                $executions++;

                throw $this->deadlock();
            });

            $this->fail('重试用尽后必须把最后一个异常原样抛出。');
        } catch (Throwable $e) {
            $this->assertSame(PDOException::class, $e::class);
            $this->assertSame(self::MYSQL_DEADLOCK, $e->getMessage());
        }

        $this->assertSame(LockRetry::ATTEMPTS, $executions);
    }

    public function test_it_does_not_retry_a_lock_wait_timeout(): void
    {
        $executions = 0;
        $exception = new PDOException(self::LOCK_WAIT_TIMEOUT);

        try {
            LockRetry::run(function () use (&$executions, $exception) {
                $executions++;

                throw $exception;
            });

            $this->fail('1205 必须原样抛出，不能重跑。');
        } catch (Throwable $e) {
            $this->assertSame($exception, $e);
        }

        $this->assertSame(1, $executions);
    }

    public function test_it_does_not_retry_a_foreign_key_violation(): void
    {
        $executions = 0;

        try {
            LockRetry::run(function () use (&$executions) {
                $executions++;

                throw new PDOException(self::FOREIGN_KEY);
            });

            $this->fail('外键冲突必须原样抛出，不能重跑。');
        } catch (Throwable $e) {
            $this->assertSame(self::FOREIGN_KEY, $e->getMessage());
        }

        $this->assertSame(1, $executions);
    }

    public function test_it_honours_a_smaller_attempt_limit(): void
    {
        $executions = 0;

        try {
            LockRetry::run(function () use (&$executions) {
                $executions++;

                throw $this->deadlock();
            }, 2);

            $this->fail('达到自定义上限后必须抛出。');
        } catch (Throwable $e) {
            $this->assertSame(self::MYSQL_DEADLOCK, $e->getMessage());
        }

        $this->assertSame(2, $executions);
    }

    public function test_a_single_attempt_limit_never_retries_even_a_deadlock(): void
    {
        $executions = 0;
        $thrown = false;

        try {
            LockRetry::run(function () use (&$executions) {
                $executions++;

                throw $this->deadlock();
            }, 1);
        } catch (Throwable) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(1, $executions);
    }

    public function test_is_deadlock_covers_the_three_database_dialects(): void
    {
        $this->assertTrue(LockRetry::isDeadlock(new PDOException(self::MYSQL_DEADLOCK)));
        $this->assertTrue(LockRetry::isDeadlock(new PDOException(self::POSTGRES_DEADLOCK)));
        $this->assertTrue(LockRetry::isDeadlock(new PDOException(self::SQL_SERVER_DEADLOCK)));
    }

    public function test_is_deadlock_rejects_lock_wait_timeout_and_foreign_key(): void
    {
        $this->assertFalse(LockRetry::isDeadlock(new PDOException(self::LOCK_WAIT_TIMEOUT)));
        $this->assertFalse(LockRetry::isDeadlock(new PDOException(self::FOREIGN_KEY)));
        $this->assertFalse(LockRetry::isDeadlock(new PDOException('SQLSTATE[HY000]: General error: 2006 MySQL server has gone away')));
    }

    public function test_a_driver_wrapped_query_exception_counts_as_a_deadlock(): void
    {
        // 驱动实际抛出的是 Illuminate\Database\QueryException（原始报错拼在消息前面），判据必须认得这一层
        $previous = new PDOException(self::MYSQL_DEADLOCK);
        $executions = 0;

        $this->assertSame(2, LockRetry::run(function () use (&$executions, $previous) {
            $executions++;

            if ($executions === 1) {
                throw new QueryException('mysql', 'insert into `node_online_ip` values (?)', [], $previous);
            }
        }));
    }

    private function deadlock(): PDOException
    {
        return new PDOException(self::MYSQL_DEADLOCK);
    }
}
