# Ordina インストールガイド

## 目次

1. [システム要件](#システム要件)
2. [開発環境のセットアップ](#開発環境のセットアップ)
3. [本番環境のセットアップ](#本番環境のセットアップ)
4. [NativePHPアプリケーションのビルド](#nativephpアプリケーションのビルド)
5. [設定](#設定)
6. [トラブルシューティング](#トラブルシューティング)

## システム要件

### 開発環境
- **PHP**: 8.1以上
- **Composer**: 2.0以上
- **Node.js**: 16.0以上
- **npm**: 7.0以上
- **Laravel Herd**: 最新版（推奨）

### 本番環境
- **Windows**: 10/11
- **macOS**: 10.15以上
- **Linux**: Ubuntu 20.04以上
- **メモリ**: 4GB以上推奨
- **ストレージ**: 1GB以上の空き容量

## 開発環境のセットアップ

### 1. リポジトリのクローン

```bash
git clone https://github.com/SilentMalachite/ordina.git
cd ordina
```

### 2. 依存関係のインストール

```bash
# Composer依存関係のインストール
composer install

# Node.js依存関係のインストール
npm install
```

### 3. 環境設定

```bash
# 環境設定ファイルの作成
cp .env.example .env

# アプリケーションキーの生成
php artisan key:generate
```

### 4. データベースの初期化

```bash
# データベースマイグレーションの実行
php artisan migrate

# シーダーの実行（初期データの投入）
php artisan db:seed
```

### 5. 開発サーバーの起動

```bash
# Laravel開発サーバーの起動
php artisan serve

# またはNativePHP開発サーバーの起動
php artisan native:serve
```

### 6. アクセス

ブラウザで以下のURLにアクセス：
- Laravel開発サーバー: `http://localhost:8000`
- NativePHP開発サーバー: 自動的にデスクトップアプリケーションが起動

## 本番環境のセットアップ

### 1. サーバー環境の準備

#### Windows
1. PHP 8.1以上をインストール
2. Composerをインストール
3. Node.js 16以上をインストール

#### macOS
```bash
# Homebrewを使用
brew install php@8.1 composer node

# またはLaravel Herdを使用（推奨）
# https://herd.laravel.com/ からダウンロード
```

#### Linux (Ubuntu)
```bash
# PHP 8.1のインストール
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-sqlite3 php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip

# Composerのインストール
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.jsのインストール
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. アプリケーションのデプロイ

```bash
# リポジトリのクローン
git clone https://github.com/SilentMalachite/ordina.git
cd ordina

# 依存関係のインストール
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 環境設定
cp .env.example .env
php artisan key:generate

# データベースの初期化
php artisan migrate --force
php artisan db:seed --force

# 権限の設定
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Webサーバーの設定

#### Apache
```apache
<VirtualHost *:80>
    ServerName ordina.local
    DocumentRoot /path/to/ordina/public
    
    <Directory /path/to/ordina/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ordina_error.log
    CustomLog ${APACHE_LOG_DIR}/ordina_access.log combined
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name ordina.local;
    root /path/to/ordina/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## NativePHPアプリケーションのビルド

### 1. ビルド環境の準備

```bash
# NativePHP CLIのインストール
composer global require nativephp/cli

# ビルドツールのインストール
npm install -g @nativephp/cli
```

### 2. アプリケーションのビルド

```bash
# 開発用ビルド
php artisan native:build

# 本番用ビルド
php artisan native:build --release
```

### 3. プラットフォーム別ビルド

```bash
# Windows用ビルド
php artisan native:build --platform=win

# macOS用ビルド
php artisan native:build --platform=mac

# Linux用ビルド
php artisan native:build --platform=linux
```

### 4. インストーラーの作成

```bash
# インストーラー付きビルド
php artisan native:build --installer
```

## 設定

### 環境変数の設定

`.env`ファイルで以下の設定を行います：

```env
# アプリケーション設定
APP_NAME="Ordina"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# データベース設定
DB_CONNECTION=sqlite
DB_DATABASE=database/ordina.sqlite

# NativePHP設定
NATIVEPHP_APP_ID=com.ordina.app
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_AUTHOR="Ordina Team <support@ordina.app>"

# Ordina設定
ORDINA_DEFAULT_CLOSING_DAY=25
ORDINA_LOW_STOCK_THRESHOLD=10
ORDINA_EXCEL_EXPORT_PATH=storage/app/exports
ORDINA_BACKUP_PATH=storage/app/backups
ORDINA_MAX_BACKUP_FILES=30
ORDINA_MAX_LOG_FILES=30
ORDINA_MAX_LOG_SIZE=10485760
```

### パフォーマンス最適化

```bash
# 設定のキャッシュ
php artisan config:cache

# ルートのキャッシュ
php artisan route:cache

# ビューのキャッシュ
php artisan view:cache

# オートローダーの最適化
composer dump-autoload --optimize
```

### セキュリティ設定

```bash
# アプリケーションキーの生成
php artisan key:generate

# セッション設定の確認
php artisan config:show session

# ファイル権限の設定
chmod -R 755 storage bootstrap/cache
chmod 644 .env

# 本番ではデバッグ無効
sed -i'' -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env || true
```

## トラブルシューティング

### よくある問題

#### 1. Composer依存関係のインストールエラー

```bash
# Composerの更新
composer self-update

# キャッシュのクリア
composer clear-cache

# 依存関係の再インストール
rm -rf vendor composer.lock
composer install
```

#### 2. Node.js依存関係のインストールエラー

```bash
# npmキャッシュのクリア
npm cache clean --force

# node_modulesの削除と再インストール
rm -rf node_modules package-lock.json
npm install
```

#### 3. データベース接続エラー

```bash
# SQLiteファイルの権限確認
ls -la database/ordina.sqlite

# 権限の修正
chmod 664 database/ordina.sqlite
chown www-data:www-data database/ordina.sqlite
```

#### 4. ストレージディレクトリの権限エラー

```bash
# ストレージディレクトリの権限設定
chmod -R 775 storage
chown -R www-data:www-data storage

# ログディレクトリの権限設定
chmod -R 775 storage/logs
```

#### 5. NativePHPビルドエラー

```bash
# NativePHP CLIの更新
composer global update nativephp/cli

# ビルドキャッシュのクリア
php artisan native:build --clear-cache

# 詳細ログでビルド
php artisan native:build --verbose
```

### ログの確認

```bash
# Laravelログの確認
tail -f storage/logs/laravel.log

# エラーログの確認
tail -f storage/logs/error.log

# システムログの確認（Linux）
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

### パフォーマンスの監視

```bash
# データベースサイズの確認
ls -lh database/ordina.sqlite

# ストレージ使用量の確認
du -sh storage/

# メモリ使用量の確認
php artisan tinker
>>> memory_get_usage(true)
```

### バックアップとリストア

```bash
# データベースのバックアップ
cp database/ordina.sqlite database/ordina_backup_$(date +%Y%m%d_%H%M%S).sqlite

# ストレージのバックアップ
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# バックアップからのリストア
cp database/ordina_backup_YYYYMMDD_HHMMSS.sqlite database/ordina.sqlite
```

### サポート

問題が解決しない場合は、以下の情報を含めてサポートに連絡してください：

1. エラーメッセージの詳細
2. システム環境（OS、PHP、Node.jsバージョン）
3. 実行したコマンド
4. ログファイルの内容
5. 設定ファイル（.env）の内容（機密情報は除く）

---

このガイドで不明な点がある場合は、プロジェクトのIssuesページで質問してください。
