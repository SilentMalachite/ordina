# Ordina - 在庫管理システム

Ordinaは、NativePHPとLaravelで構築された小規模〜中規模組織向けの在庫管理システムです。

## 特徴

- **使いやすいGUI**: PCに不慣れな方でも直感的に操作できるインターフェース
- **マルチプラットフォーム対応**: Windows、macOS、Linuxで動作
- **オフライン動作**: SQLiteデータベースを内蔵し、インターネット接続不要
- **セキュアなアクセス管理**: ログイン機能と管理者権限による階層的なアクセス制御
- **柔軟なデータ管理**: 商品の登録、在庫管理、売上・貸出管理
- **レポート機能**: 期間別・取引先別のレポート生成とExcel出力
- **データインポート**: Excelファイルからのデータ一括取り込み

## 動作環境

### 開発環境
- PHP 8.1以上
- Composer
- Node.js 16以上
- npm または yarn
- Laravel Herd（推奨）

### 本番環境
- Windows 10/11、macOS 10.15以上、Ubuntu 20.04以上

## インストール

### 開発環境のセットアップ

1. リポジトリをクローン
```bash
git clone https://github.com/SilentMalachite/ordina.git
cd ordina
```

2. 依存関係のインストール
```bash
composer install
npm install
```

3. 環境設定ファイルの作成
```bash
cp .env.example .env
php artisan key:generate
```

4. データベースの初期化
```bash
php artisan migrate
php artisan db:seed
```

5. 開発サーバーの起動
```bash
php artisan native:serve
```

### ビルド済みアプリケーションのインストール

[Releases](https://github.com/SilentMalachite/ordina/releases)ページから、お使いのOSに対応したインストーラーをダウンロードしてください。

## 使い方

### 初回起動時

1. アプリケーションを起動します
2. 管理者アカウントでログインします（初期パスワードは初回起動時に設定）
3. システム設定から基本情報を設定します

### 基本操作

#### 商品登録
1. メニューから「商品管理」を選択
2. 「新規登録」ボタンをクリック
3. 商品コード、品名、単価、売値を入力
4. 「保存」をクリック

#### 在庫管理
1. メニューから「在庫管理」を選択
2. 商品を検索または一覧から選択
3. 入庫・出庫数を入力
4. 取引先を選択（売上・貸出の場合）
5. 「確定」をクリック

#### レポート出力
1. メニューから「レポート」を選択
2. 期間と出力形式を選択
3. 必要に応じて取引先でフィルタリング
4. 「Excel出力」をクリック

## 技術仕様

- **フレームワーク**: Laravel 10.x
- **デスクトップ化**: NativePHP
- **データベース**: SQLite（内蔵）
- **UI**: Blade + Alpine.js + Tailwind CSS
- **認証**: Laravel Breeze
- **Excel処理**: Laravel Excel

## 設定の概要

- `config/ordina.php`: Ordina固有の設定を集約
  - `default_closing_day`: 締め日の既定値（ENV: `ORDINA_DEFAULT_CLOSING_DAY`）
  - `low_stock_threshold`: 低在庫のしきい値（ENV: `ORDINA_LOW_STOCK_THRESHOLD`）
  - `excel_export_path`: Excel出力先（ENV: `ORDINA_EXCEL_EXPORT_PATH`）
  - `backup_path`: バックアップ保存先（ENV: `ORDINA_BACKUP_PATH`）
  - `max_backup_files`: バックアップ保持数（ENV: `ORDINA_MAX_BACKUP_FILES`）
  - `max_log_files` / `max_log_size`: ログローテーション設定（ENV: `ORDINA_MAX_LOG_FILES`, `ORDINA_MAX_LOG_SIZE`）

注意: `.env` は既定で本番向けに `APP_DEBUG=false` です。

## 権限とロール

- 権限（抜粋）
  - 商品: `product-list`, `product-create`, `product-edit`, `product-delete`
  - 在庫: `inventory-view`, `inventory-adjust`, `inventory-bulk-adjust`
  - 顧客: `customer-list`, `customer-create`, `customer-edit`, `customer-delete`
  - 取引: `transaction-list`, `transaction-create`, `transaction-edit`, `transaction-delete`, `transaction-return`
  - レポート: `report-view`, `report-export`
  - インポート: `import-run`
  - システム: `system-manage`, `user-manage`, `role-manage`, `closing-date-manage`
  - ログ/バックアップ: `log-view`, `log-manage`, `backup-view`, `backup-manage`
  - 同期: `sync-conflicts-view`, `sync-conflicts-resolve`
  - APIトークン: `api-token-view`, `api-token-create`, `api-token-edit`, `api-token-delete`

- 代表ロール
  - 管理者: 全権限（Gate::before でも全許可）
  - マネージャー: 管理以外の大半 + ログ/バックアップ閲覧
  - 一般ユーザー: 閲覧 + 作成/更新中心（インポート含む）
  - 閲覧者: 一覧/閲覧のみ

権限の初期化: `php artisan db:seed --class=RolesAndPermissionsSeeder`

移行メモ: 旧 `inventory-list` は `inventory-view` に統一されました。

## 開発

### ディレクトリ構造
```
ordina/
├── app/                  # アプリケーションロジック
├── config/              # 設定ファイル
├── database/            # マイグレーション、シーダー
├── public/              # 公開ディレクトリ
├── resources/           # ビュー、アセット
├── routes/              # ルーティング
├── storage/             # ログ、キャッシュ
└── tests/               # テスト
```

### テストの実行
```bash
php artisan test
```

### コードスタイル
```bash
./vendor/bin/pint
```

## 貢献

プロジェクトへの貢献を歓迎します。詳細は[CONTRIBUTING.md](CONTRIBUTING.md)をご覧ください。

## ライセンス

このプロジェクトはMITライセンスのもとで公開されています。詳細は[LICENSE](LICENSE)ファイルをご覧ください。

## サポート

- 問題の報告: [Issues](https://github.com/SilentMalachite/ordina/issues)
- ディスカッション: [Discussions](https://github.com/SilentMalachite/ordina/discussions)

## クレジット

- Laravel - The PHP Framework for Web Artisans
- NativePHP - Build native desktop applications using PHP

## セキュリティ注記

- バックアップに `.env` は含みません。管理画面からのダウンロードは安全なファイル名・パスのみ許可します（拡張子制限 + 実パス検証）。
- APIトークンは `Authorization: Bearer <token>` での利用を推奨します（クエリ/フォームでの指定は将来廃止予定）。
