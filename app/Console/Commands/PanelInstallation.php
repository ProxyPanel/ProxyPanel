<?php

namespace App\Console\Commands;

use App\Models\User;
use Artisan;
use Exception;
use File;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PanelInstallation extends Command
{
    protected $signature = 'panel:install';

    protected $description = 'ProxyPanel Installation (面板自主安装)';

    public function handle(): int
    {
        $bar = $this->output->createProgressBar(7);
        $bar->minSecondsBetweenRedraws(0);

        $this->displayBanner();
        $bar->start();

        $this->createEnvironmentFile();
        $bar->advance();

        $this->setDatabaseInfo(); // 设置数据库信息
        $bar->advance();

        $this->generateAppKey(); // 设置 app key
        $bar->advance();

        $this->testDatabaseConnection(); // 测试数据库连通性
        $bar->advance();

        $this->importDatabase(); // 初始化数据库
        $bar->advance();

        $this->createStorageLink(); // 文件夹软连接
        $bar->advance();

        $this->setAdminAccount(); // 设置管理员信息
        $bar->finish();
        $this->info('Initial installation completed! | 初步安装完毕');

        $this->line(PHP_EOL.'View your http(s)://[website url]/admin to insert Administrator Dashboard | 访问 http(s)://[你的站点]/admin 进入管理面板');

        return 0;
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

    private function createEnvironmentFile(): void
    {
        $this->line('  Creating .env | 创建.env');
        $envPath = app()->environmentFilePath();

        if (File::exists($envPath)) {
            $this->error('.env file already exists. | .env已存在');

            if (! $this->confirm('Do you wish to continue by deleting the existing .env file? | 是否删除已存.env文件, 并继续安装?')) {
                abort(500, 'Installation aborted by user decision. | 安装程序终止');
            }

            File::delete($envPath);
        }

        try {
            File::copy(base_path('.env.example'), $envPath);
        } catch (Exception $e) {
            abort(500, 'Failed to copy .env.example to .env file. Please check file permissions. | 复制环境文件失败, 请检查目录权限 '.$e->getMessage());
        }
    }

    /**
     * @throws FileNotFoundException
     */
    private function setDatabaseInfo(): void
    {
        $this->line(' Setting up database information | 设置数据库信息');

        $databaseInfo = [
            'DB_HOST' => $this->ask('Enter the database host (default: localhost) | 请输入数据库地址（默认:localhost）', 'localhost'),
            'DB_PORT' => $this->ask('Enter the database port (default: 3306) | 请输入数据库地址（默认:3306）', 3306),
            'DB_DATABASE' => $this->ask('Enter the database name | 请输入数据库名'),
            'DB_USERNAME' => $this->ask('Enter the database username | 请输入数据库用户名'),
            'DB_PASSWORD' => $this->ask('Enter the database password | 请输入数据库密码'),
        ];

        $this->saveToEnv($databaseInfo);
    }

    /**
     * @throws FileNotFoundException
     */
    private function saveToEnv(array $data = []): void
    {
        $envPath = app()->environmentFilePath();
        $contents = File::get($envPath);

        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            $value = Str::contains($value, ' ') ? '"'.$value.'"' : $value;
            $line = $key.'='.$value;

            $contents = preg_replace("/^$key=[^\r\n]*/m", $line, $contents, -1, $count);

            if ($count === 0) {
                $contents .= "\n".$line;
            }
        }

        File::put($envPath, $contents);
        $this->line('.env file updated successfully. | .env文件更新成功');
    }

    private function generateAppKey(): void
    {
        $this->line('  Generating app key | 设置 app key');

        Artisan::call('key:generate');
    }

    private function testDatabaseConnection(): void
    {
        $this->line(' Testing database connection | 测试数据库连通性');
        try {
            Artisan::call('config:cache');
            DB::connection()->getPdo();
        } catch (Exception $e) {
            File::delete(app()->environmentFilePath());
            abort(500, 'Failed to connect to the database: | 数据库连接失败: '.$e->getMessage());
        }
    }

    private function importDatabase(): void
    {
        $this->line(' Importing database | 导入数据库');
        try {
            Artisan::call('migrate --seed');
        } catch (Exception $e) {
            Artisan::call('db:wipe');
            abort(500, 'Failed to import database: | 导入数据库失败: '.$e->getMessage());
        }
        $this->info('Database loaded! | 数据库导入完成');
    }

    private function createStorageLink(): void
    {
        $this->line(' Creating storage link | 建立文件夹软连接');
        try {
            Artisan::call('storage:link');
        } catch (Exception $e) {
            abort(500, 'Failed to create storage link: | 建立文件夹软连接失败: '.$e->getMessage());
        }
    }

    private function setAdminAccount(): void
    {
        $this->line(' Setting up admin account | 设置管理员基础信息');

        $username = $this->ask('Please set your administrator account email address | 请输入[管理员]邮箱 默认: test@test.com', 'test@test.com');
        $this->info('[管理员] 账号: '.$username);

        $password = $this->ask('Please set your administrator account password | 请输入[管理员]密码 默认: 123456', '123456');
        $this->info('[管理员] 密码: '.$password);

        if ($this->editAdmin($username, $password)) {
            $this->info('Admin account created successfully. | 管理员账号创建成功');
        } else {
            abort(500, '管理员账号注册失败，请重试');
        }

        $this->info('Admin account created successfully.');
    }

    private function editAdmin(string $username, string $password): bool
    {
        $user = User::find(1);
        $user->username = $username;
        $user->password = $password;

        return $user->save();
    }
}
