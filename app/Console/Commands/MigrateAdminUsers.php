<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MigrateAdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:migrate-admins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '既存のis_adminフラグを持つユーザーを管理者ロールに移行';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('既存の管理者ユーザーを移行しています...');

        $adminUsers = User::where('is_admin', true)->get();
        
        if ($adminUsers->isEmpty()) {
            $this->info('管理者ユーザーが見つかりませんでした。');
            return;
        }

        foreach ($adminUsers as $user) {
            $user->assignRole('管理者');
            $this->info("ユーザー {$user->name} ({$user->email}) に管理者ロールを割り当てました。");
        }

        // 一般ユーザーにデフォルトロールを割り当て
        $normalUsers = User::where('is_admin', false)->get();
        
        foreach ($normalUsers as $user) {
            if (!$user->hasAnyRole()) {
                $user->assignRole('一般スタッフ');
                $this->info("ユーザー {$user->name} ({$user->email}) に一般スタッフロールを割り当てました。");
            }
        }

        $this->info('移行が完了しました。');
    }
}