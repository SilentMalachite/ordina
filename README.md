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