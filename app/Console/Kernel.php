<?php

namespace App\Console;

use App\Console\Commands\NodeBlockedDetection;
use App\Console\Commands\AutoClearLog;
use App\Console\Commands\AutoDecGoodsTraffic;
use App\Console\Commands\AutoJob;
use App\Console\Commands\AutoReportNode;
use App\Console\Commands\AutoResetUserTraffic;
use App\Console\Commands\AutoStatisticsNodeDailyTraffic;
use App\Console\Commands\AutoStatisticsNodeHourlyTraffic;
use App\Console\Commands\AutoStatisticsUserDailyTraffic;
use App\Console\Commands\AutoStatisticsUserHourlyTraffic;
use App\Console\Commands\upgradeUserLabels;
use App\Console\Commands\upgradeUserPassword;
use App\Console\Commands\upgradeUserSpeedLimit;
use App\Console\Commands\upgradeUserSubscribe;
use App\Console\Commands\upgradeUserVmessId;
use App\Console\Commands\UserExpireAutoWarning;
use App\Console\Commands\UserTrafficAbnormalAutoWarning;
use App\Console\Commands\UserTrafficAutoWarning;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		AutoJob::class,
		AutoClearLog::class,
		AutoDecGoodsTraffic::class,
		AutoResetUserTraffic::class,
		NodeBlockedDetection::class,
		AutoStatisticsNodeDailyTraffic::class,
		AutoStatisticsNodeHourlyTraffic::class,
		AutoStatisticsUserDailyTraffic::class,
		AutoStatisticsUserHourlyTraffic::class,
		UserTrafficAbnormalAutoWarning::class,
		UserExpireAutoWarning::class,
		UserTrafficAutoWarning::class,
		upgradeUserLabels::class,
		upgradeUserPassword::class,
		upgradeUserSpeedLimit::class,
		upgradeUserSubscribe::class,
		upgradeUserVmessId::class,
		AutoReportNode::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param Schedule $schedule
	 *
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('autoJob')->everyMinute();
		$schedule->command('autoClearLog')->everyThirtyMinutes();
		$schedule->command('autoDecGoodsTraffic')->everyTenMinutes();
		$schedule->command('autoResetUserTraffic')->daily();
		$schedule->command('NodeBlockedDetection')->everyThirtyMinutes();
		$schedule->command('autoStatisticsNodeDailyTraffic')->dailyAt('23:55');
		$schedule->command('autoStatisticsNodeHourlyTraffic')->hourly();
		$schedule->command('autoStatisticsUserDailyTraffic')->dailyAt('23:50');
		$schedule->command('autoStatisticsUserHourlyTraffic')->hourly();
		$schedule->command('userTrafficAbnormalAutoWarning')->hourly();
		$schedule->command('userExpireAutoWarning')->dailyAt('20:00');
		$schedule->command('userTrafficAutoWarning')->dailyAt('10:30');
		$schedule->command('autoReportNode')->dailyAt('09:00');
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__.'/Commands');

		require base_path('routes/console.php');
	}
}
