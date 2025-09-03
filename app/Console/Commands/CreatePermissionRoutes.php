<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionService;
use App\Models\User;

class CreatePermissionRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default permissions and roles for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating permissions and roles...');

        $permissionService = new PermissionService();
        
        // 権限とロールの初期化
        $permissionService->initializePermissions();
        $this->info('Permissions and roles created successfully.');

        // デフォルト管理者ユーザーの作成
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@ordina.local'],
            [
                'name' => 'システム管理者',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // 管理者権限を付与
        $permissionService->grantAdminRole($adminUser);

        $this->info('Default admin user created successfully.');
        $this->info('Email: admin@ordina.local');
        $this->info('Password: password');
        $this->warn('Please change the default password in production!');

        return 0;
    }
}