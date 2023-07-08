<?php

namespace App\Console\Commands;

use Artisan;
use Exception;
use Illuminate\Console\Command;

class PanelUpdate extends Command
{
    protected $signature = 'panel:update';

    protected $description = 'ProxyPanel Version Update (面板更新)';

    public function handle(): void
    {
        try {
            $bar = $this->output->createProgressBar(2);
            $bar->minSecondsBetweenRedraws(0);
            $this->info('   ___                              ___                      _ '.PHP_EOL."  / _ \ _ __   ___  __  __ _   _   / _ \  __ _  _ __    ___ | |".PHP_EOL." / /_)/| '__| / _ \ \ \/ /| | | | / /_)/ / _` || '_ \  / _ \| |".PHP_EOL.'/ ___/ | |   | (_) | >  < | |_| |/ ___/ | (_| || | | ||  __/| |'.PHP_EOL."\/     |_|    \___/ /_/\_\ \__, |\/      \__,_||_| |_| \___||_|".PHP_EOL.'                           |___/                               '.PHP_EOL);
            $bar->start();
            $this->line(' 更新数据库...');
            Artisan::call('migrate --force');

            if (config('app.env') === 'demo' && $this->confirm('检测到您在DEMO模式, 是否重置数据库?')) {
                Artisan::call('migrate:fresh --seed --force');
            }

            $bar->advance();
            $this->line(' 更新缓存...');
            Artisan::call('optimize');
            $bar->finish();
            $this->info(' 更新完毕! ');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
