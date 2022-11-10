<?php

namespace App\Console\Commands;

use Artisan;
use Exception;
use Illuminate\Console\Command;

class PanelUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panel:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ProxyPanel Version Update (面板更新)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $bar = $this->output->createProgressBar(2);
            $bar->minSecondsBetweenRedraws(0);
            $this->info('   ___                              ___                      _ '.PHP_EOL."  / _ \ _ __   ___  __  __ _   _   / _ \  __ _  _ __    ___ | |".PHP_EOL." / /_)/| '__| / _ \ \ \/ /| | | | / /_)/ / _` || '_ \  / _ \| |".PHP_EOL.'/ ___/ | |   | (_) | >  < | |_| |/ ___/ | (_| || | | ||  __/| |'.PHP_EOL."\/     |_|    \___/ /_/\_\ \__, |\/      \__,_||_| |_| \___||_|".PHP_EOL.'                           |___/                               '.PHP_EOL);
            $bar->start();
            $this->line(' 更新数据库...');
            Artisan::call('migrate --force');
            $bar->advance();
            $this->line(' 更新缓存...');
            Artisan::call('optimize');
            $bar->finish();
            $this->info(' 更新完毕! ');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }
}
