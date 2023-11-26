<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Config;
use App\Models\Country;
use App\Models\EmailFilter;
use App\Models\Label;
use App\Models\Level;
use App\Models\Rule;
use App\Models\SsConfig;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Config::doesntExist()) {
            $this->call(ConfigSeeder::class);
        }
        if (Level::doesntExist()) {
            $this->call(LevelSeeder::class);
        }
        if (Role::doesntExist()) {
            $this->call(RBACSeeder::class);
        }
        if (User::doesntExist()) {
            // 生成初始管理账号
            $user = Helpers::addUser('test@test.com', '123456', 100 * GiB, (int) sysConfig('default_days'), null, '管理员');
            $user->update(['status' => 1]);
            $user->assignRole('Super Admin');
        }
        if (Country::doesntExist()) {
            $this->call(CountrySeeder::class);
        }
        if (Label::doesntExist()) {
            $this->call(LabelSeeder::class);
        }
        if (Rule::doesntExist()) {
            $this->call(RuleSeeder::class);
        }
        if (SsConfig::doesntExist()) {
            $this->call(SsConfigSeeder::class);
        }
        if (EmailFilter::doesntExist()) {
            $this->call(EmailFilterSeeder::class);
        }
        if (Article::doesntExist()) {
            $this->call(ArticleSeeder::class);
        }
    }
}
