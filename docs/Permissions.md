# 権限とロール

本ドキュメントは、Ordinaで使用される権限とロールの一覧、および初期化手順を説明します。

## 権限一覧

- 商品
  - `product-list`, `product-create`, `product-edit`, `product-delete`
- 在庫
  - `inventory-view`, `inventory-adjust`, `inventory-bulk-adjust`
- 顧客
  - `customer-list`, `customer-create`, `customer-edit`, `customer-delete`
- 取引
  - `transaction-list`, `transaction-create`, `transaction-edit`, `transaction-delete`, `transaction-return`
- レポート
  - `report-view`, `report-export`
- インポート
  - `import-run`
- システム
  - `system-manage`, `user-manage`, `role-manage`, `closing-date-manage`
- ログ/バックアップ
  - `log-view`, `log-manage`, `backup-view`, `backup-manage`
- 同期
  - `sync-conflicts-view`, `sync-conflicts-resolve`
- APIトークン
  - `api-token-view`, `api-token-create`, `api-token-edit`, `api-token-delete`

## ロール構成（推奨）

- 管理者（Administrator）
  - すべての権限（Gate::before により常に許可）
- マネージャー（Manager）
  - 作成/編集/削除の大半 + ログ/バックアップ閲覧 + 同期競合閲覧 + APIトークン閲覧
- 一般ユーザー（Staff）
  - 一覧/作成/編集 + 在庫調整 + レポート閲覧/出力 + インポート実行
- 閲覧者（Viewer）
  - 閲覧のみ（一覧系）

## 初期化

以下のいずれかで初期化できます。

1) Seeder 実行
```
php artisan db:seed --class=RolesAndPermissionsSeeder
```

2) コマンド実行（権限・ロール作成 + デフォルト管理者作成）
```
php artisan permission:create-permission-routes
```

## 互換性メモ（移行）

- 旧権限名 `inventory-list` は `inventory-view` に変更されました。
  - 既存DBにエントリーがある場合は以下のいずれかを実施してください。
    - Seeder を再実行して権限を再同期
    - 手動更新: `UPDATE permissions SET name='inventory-view' WHERE name='inventory-list';`

## ベストプラクティス

- ロールに権限を付与し、ユーザーにはロールを割り当てる（直接権限付与は最小限に）。
- 管理者は Gate::before により全許可のため、権限の個別付与は不要です。

