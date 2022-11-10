<?php

namespace App\Console\Commands;

use App\Models\User;
use Artisan;
use Exception;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PanelInstallation extends Command
{
    protected $signature = 'panel:install';
    protected $description = 'ProxyPanel Installation (面板自主安装)';

    public function handle()
    {
        try {
            $bar = $this->output->createProgressBar(7);
            $bar->minSecondsBetweenRedraws(0);

            $this->info(PHP_EOL.'   ___                              ___                      _ '.PHP_EOL."  / _ \ _ __   ___  __  __ _   _   / _ \  __ _  _ __    ___ | |".PHP_EOL." / /_)/| '__| / _ \ \ \/ /| | | | / /_)/ / _` || '_ \  / _ \| |".PHP_EOL.'/ ___/ | |   | (_) | >  < | |_| |/ ___/ | (_| || | | ||  __/| |'.PHP_EOL."\/     |_|    \___/ /_/\_\ \__, |\/      \__,_||_| |_| \___||_|".PHP_EOL.'                           |___/                               '.PHP_EOL);

            $bar->start();
            $this->line(' 创建.env');
            if (File::exists(base_path().'/.env')) {
                $this->error('.env existed | .env已存在');
                if ($this->confirm('Do you wish to continue by deleting current exist .env file? | 是否删除已存.env文件, 并继续安装?', true)) {
                    File::delete(base_path().'/.env');
                } else {
                    abort(500, 'Installation aborted by user decision. | 安装程序终止');
                }
            }
            if (! copy(base_path().'/.env.example', base_path().'/.env')) {
                abort(500, 'copy .env.example to .env failed, please check file permissions | 复制环境文件失败，请检查目录权限');
            }
            $bar->advance();

            // 设置数据库信息
            $this->line(' 设置数据库信息');
            $this->saveToEnv([
                'DB_HOST'     => $this->ask('请输入数据库地址（默认:localhost）', 'localhost'),
                'DB_PORT'     => $this->ask('请输入数据库地址（默认:3306）', 3306),
                'DB_DATABASE' => $this->ask('请输入数据库名'),
                'DB_USERNAME' => $this->ask('请输入数据库用户名'),
                'DB_PASSWORD' => $this->ask('请输入数据库密码'),
            ]);
            $bar->advance();

            // 设置 app key
            $this->line(' 设置 app key');
            Artisan::call('key:generate');
            $bar->advance();

            // 测试数据库连通性
            $this->line(' 测试数据库连通性');
            try {
                Artisan::call('config:cache');
                DB::connection()->getPdo();
            } catch (Exception $e) {
                File::delete(base_path().'/.env');
                abort(500, '数据库连接失败'.$e->getMessage());
            }
            $bar->advance();

            // 初始化数据库
            $this->line(' 导入数据库');
            Artisan::call('migrate --seed');
            $this->info('数据库导入完成');
            $bar->advance();

            // 文件夹软连接
            $this->line(' 建立文件夹软连接');
            Artisan::call('storage:link');
            $bar->advance();

            // 设置 管理员基础信息
            $this->line(' 设置管理员基础信息');
            $email = '';
            while (! $email) {
                $email = $this->ask('Please set your administrator account email address | 请输入[管理员]邮箱 默认: test@test.com', 'test@test.com');
                $this->info('[管理员] 账号：'.$email);
            }
            $password = '';
            while (! $password) {
                $password = $this->ask('Please set your administrator account password | 请输入[管理员]密码 默认: 123456', '123456');
                $this->info('[管理员]密码：'.$password);
            }
            if (! $this->editAdmin($email, $password)) {
                abort(500, '管理员账号注册失败，请重试');
            }
            $bar->finish();
            $this->info(' Initial installation Completed! | 初步安装完毕');

            $this->line(PHP_EOL.'View your http(s)://[website url]/admin to insert Administrator Dashboard | 访问 http(s)://[你的站点]/admin 进入管理面板');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }

    private function saveToEnv($data = [])
    {
        function set_env_var($key, $value): bool
        {
            if (! is_bool(strpos($value, ' '))) {
                $value = '"'.$value.'"';
            }
            $key = strtoupper($key);

            $envPath = app()->environmentFilePath();
            $contents = file_get_contents($envPath);

            preg_match("/^{$key}=[^\r\n]*/m", $contents, $matches);

            $oldValue = count($matches) ? $matches[0] : '';

            if ($oldValue) {
                $contents = str_replace((string) ($oldValue), "{$key}={$value}", $contents);
            } else {
                $contents .= "\n{$key}={$value}\n";
            }

            $file = fopen($envPath, 'wb');
            fwrite($file, $contents);

            return fclose($file);
        }

        foreach ($data as $key => $value) {
            set_env_var($key, $value);
        }

        return true;
    }

    private function editAdmin($email, $password)
    {
        $user = User::find(1);
        $user->username = $email;
        $user->password = $password;

        return $user->save();
    }
}
