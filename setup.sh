#!/bin/bash

# Ordina セットアップスクリプト
# このスクリプトは開発環境の初期セットアップを自動化します

set -e  # エラーが発生したら停止

echo "🚀 Ordina セットアップを開始します..."

# 1. Composer依存関係のインストール
echo "📦 Composer依存関係をインストール中..."
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    echo "✅ Composer依存関係のインストールが完了しました"
else
    echo "⚠️  vendorディレクトリが既に存在します。スキップします。"
fi

# 2. 環境設定ファイルの作成
echo "⚙️  環境設定ファイルを作成中..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ .envファイルを作成しました"
    
    # アプリケーションキーの生成
    echo "🔑 アプリケーションキーを生成中..."
    php artisan key:generate --no-interaction
    echo "✅ アプリケーションキーを生成しました"
else
    echo "⚠️  .envファイルが既に存在します。スキップします。"
fi

# 3. データベースディレクトリの作成
echo "🗄️  データベースディレクトリを作成中..."
mkdir -p database
echo "✅ データベースディレクトリを作成しました"

# 4. ストレージディレクトリの権限設定
echo "📁 ストレージディレクトリの権限を設定中..."
mkdir -p storage/app/backups
mkdir -p storage/app/exports
mkdir -p storage/logs
chmod -R 775 storage
echo "✅ ストレージディレクトリの権限を設定しました"

# 5. データベースの初期化
echo "🗃️  データベースを初期化中..."
if [ ! -f "database/ordina.sqlite" ]; then
    touch database/ordina.sqlite
    echo "✅ SQLiteデータベースファイルを作成しました"
fi

# マイグレーションの実行
echo "🔄 データベースマイグレーションを実行中..."
php artisan migrate --force --no-interaction
echo "✅ データベースマイグレーションが完了しました"

# 6. シーダーの実行
echo "🌱 データベースシーダーを実行中..."
php artisan db:seed --force --no-interaction
echo "✅ データベースシーダーが完了しました"

# 7. 権限とロールの初期化
echo "👥 権限とロールを初期化中..."
php artisan permission:create-permission-routes
echo "✅ 権限とロールの初期化が完了しました"

# 8. キャッシュのクリア
echo "🧹 キャッシュをクリア中..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✅ キャッシュのクリアが完了しました"

# 9. 設定の最適化
echo "⚡ 設定を最適化中..."
php artisan config:cache
php artisan route:cache
echo "✅ 設定の最適化が完了しました"

echo ""
echo "🎉 Ordina セットアップが完了しました！"
echo ""
echo "次のステップ:"
echo "1. 開発サーバーを起動: php artisan native:serve"
echo "2. またはWebサーバーを起動: php artisan serve"
echo ""
echo "デフォルトの管理者アカウント:"
echo "Email: admin@ordina.local"
echo "Password: password"
echo ""
echo "⚠️  本番環境では必ずパスワードを変更してください！"