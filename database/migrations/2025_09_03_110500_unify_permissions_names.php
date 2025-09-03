<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1) inventory-list -> inventory-view への統一
            $old = DB::table('permissions')->where('name', 'inventory-list')->first();
            $new = DB::table('permissions')->where('name', 'inventory-view')->first();

            if ($old && !$new) {
                DB::table('permissions')->where('id', $old->id)->update(['name' => 'inventory-view']);
            } elseif ($old && $new) {
                // 既に新名称がある場合は、ロール紐付けを新IDへ集約して旧レコード削除
                DB::table('role_has_permissions')->where('permission_id', $old->id)->update(['permission_id' => $new->id]);
                DB::table('permissions')->where('id', $old->id)->delete();
            } else {
                // どちらも無い場合は新名称を作成
                if (!$new) {
                    DB::table('permissions')->insert([
                        'name' => 'inventory-view',
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 2) import-run の作成 + 旧インポート権限からの移行
            $importRun = DB::table('permissions')->where('name', 'import-run')->first();
            if (!$importRun) {
                DB::table('permissions')->insert([
                    'name' => 'import-run',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $importRun = DB::table('permissions')->where('name', 'import-run')->first();
            }

            $legacyImports = DB::table('permissions')->whereIn('name', [
                'import-products', 'import-customers', 'import-transactions',
            ])->get();

            foreach ($legacyImports as $legacy) {
                // 旧権限を持っている全ロールに import-run を付与
                $roles = DB::table('role_has_permissions')->where('permission_id', $legacy->id)->pluck('role_id');
                foreach ($roles as $roleId) {
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $importRun->id)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $importRun->id,
                        ]);
                    }
                }
            }

            // 旧インポート権限を削除（任意）
            if ($legacyImports->count() > 0) {
                DB::table('permissions')->whereIn('id', $legacyImports->pluck('id'))->delete();
            }

            // 3) 新規権限の作成（存在しない場合）
            $ensurePerms = [
                'role-manage',
                'sync-conflicts-view', 'sync-conflicts-resolve',
                'api-token-view', 'api-token-create', 'api-token-edit', 'api-token-delete',
                'log-view', 'log-manage', 'backup-view', 'backup-manage',
            ];
            foreach ($ensurePerms as $p) {
                $exists = DB::table('permissions')->where('name', $p)->exists();
                if (!$exists) {
                    DB::table('permissions')->insert([
                        'name' => $p,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 4) 代表ロールへの割当（存在する場合のみ）
            $permId = fn(string $name) => optional(DB::table('permissions')->where('name', $name)->first())->id;
            $assign = function (string $roleName, array $permNames) use ($permId) {
                $role = DB::table('roles')->where('name', $roleName)->first();
                if (!$role) return;
                foreach ($permNames as $name) {
                    $pid = $permId($name);
                    if (!$pid) continue;
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $pid)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $role->id,
                            'permission_id' => $pid,
                        ]);
                    }
                }
            };

            // マネージャー: ログ/バックアップ閲覧 + 同期競合閲覧 + APIトークン閲覧
            $assign('マネージャー', ['log-view', 'backup-view', 'sync-conflicts-view', 'api-token-view']);

            // 一般スタッフ: import-run を確実に付与
            $assign('一般スタッフ', ['import-run']);

            // 閲覧者: inventory-view を確実に付与（名称変更の後方互換）
            $assign('閲覧者', ['inventory-view']);
        });

        // Spatie Permission のキャッシュをクリア（存在すれば）
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // no-op on failure
        }
    }

    public function down(): void
    {
        // 破壊的変更（名称統一）はdownで戻さない
    }
};

