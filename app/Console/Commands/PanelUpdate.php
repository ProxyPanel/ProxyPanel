<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class PanelUpdate extends Command
{
    protected $signature = 'panel:update';

    protected $description = 'ProxyPanel Version Update (面板更新)';

    public function handle(): void
    {
        $bar = $this->output->createProgressBar(2);
        $bar->minSecondsBetweenRedraws(0);
        $this->displayBanner();
        $bar->start();
        $this->updateDatabase();

        $bar->advance();
        $this->updateCache();

        $bar->finish();
        $this->info(trans('setup.update_complete'));
    }

    private function displayBanner(): void
    {
        $banner = <<<BANNER
   ___                              ___                      _ 
  / _ \ _ __   ___  __  __ _   _   / _ \  __ _  _ __    ___ | |
 / /_)/| '__| / _ \ \ \/ /| | | | / /_)/ / _` || '_ \  / _ \| |
/ ___/ | |   | (_) | >  < | |_| |/ ___/ | (_| || | | ||  __/| |
\/     |_|    \___/ /_/\_\ \__, |\/      \__,_||_| |_| \___||_|
                           |___/                               

BANNER;

        $this->info($banner);
    }

    private function updateDatabase(): void
    {
        $this->line(trans('setup.update_db'));
        Artisan::call('migrate --force');

        if (config('app.env') === 'demo' && $this->confirm(trans('setup.demo_reset'))) {
            Artisan::call('migrate:fresh --seed --force');
        }
    }

    private function updateCache(): void
    {
        $this->line(trans('setup.update_cache'));
        Artisan::call('optimize');
    }
}
