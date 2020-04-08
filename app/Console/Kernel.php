<?php

namespace App\Console;

use App\Console\Commands\AutoClearLog;
use App\Console\Commands\AutoJob;
use App\Console\Commands\AutoPingNode;
use App\Console\Commands\AutoReportNode;
use App\Console\Commands\AutoStatisticsNodeDailyTraffic;
use App\Console\Commands\AutoStatisticsNodeHourlyTraffic;
use App\Console\Commands\AutoStatisticsUserDailyTraffic;
use App\Console\Commands\AutoStatisticsUserHourlyTraffic;
use App\Console\Commands\DailyJob;
use App\Console\Commands\NodeBlockedDetection;
use App\Console\Commands\ServiceTimer;
use App\Console\Commands\upgradeUserResetTime;
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
		AutoClearLog::class,
		AutoJob::class,
		AutoPingNode::class,
		AutoReportNode::class,
		AutoStatisticsNodeDailyTraffic::class,
		AutoStatisticsNodeHourlyTraffic::class,
		AutoStatisticsUserDailyTraffic::class,
		AutoStatisticsUserHourlyTraffic::class,
		DailyJob::class,
		NodeBlockedDetection::class,
		ServiceTimer::class,
		upgradeUserResetTime::class,
		UserExpireAutoWarning::class,
		UserTrafficAbnormalAutoWarning::class,
		UserTrafficAutoWarning::class,
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
		$schedule->command('serviceTimer')->everyTenMinutes();
		$schedule->command('autoClearLog')->everyThirtyMinutes();
		$schedule->command('nodeBlockedDetection')->everyTenMinutes();
		$schedule->command('autoStatisticsNodeHourlyTraffic')->hourly();
		$schedule->command('autoStatisticsUserHourlyTraffic')->hourly();
		$schedule->command('userTrafficAbnormalAutoWarning')->hourly();
		$schedule->command('autoPingNode')->twiceDaily();
		$schedule->command('dailyJob')->daily();
		$schedule->command('autoReportNode')->dailyAt('09:00');
		$schedule->command('userTrafficAutoWarning')->dailyAt('10:30');
		$schedule->command('userExpireAutoWarning')->dailyAt('20:00');
		$schedule->command('autoStatisticsUserDailyTraffic')->dailyAt('23:50');
		$schedule->command('autoStatisticsNodeDailyTraffic')->dailyAt('23:55');
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
