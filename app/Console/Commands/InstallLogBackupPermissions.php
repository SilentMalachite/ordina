<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallLogBackupPermissions extends Command
{
    protected $signature = 'permission:install-log-backup';
    protected $description = 'Create log/backup permissions and assign to roles';

    public function handle()
    {
        $perms = ['log-view','log-manage','backup-view','backup-manage'];
        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }

        $admin = Role::where('name', '管理者')->first();
        if ($admin) {
            $admin->givePermissionTo(['log-view','log-manage','backup-view','backup-manage']);
            $this->info('管理者にログ/バックアップの全権限を付与しました');
        }

        $manager = Role::where('name', 'マネージャー')->first();
        if ($manager) {
            $manager->givePermissionTo(['log-view','backup-view']);
            $this->info('マネージャーに閲覧権限を付与しました');
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}

