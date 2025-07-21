# Ordina への貢献

Ordinaプロジェクトへの貢献を検討いただき、ありがとうございます！

## 行動規範

このプロジェクトに参加するすべての人は、礼儀正しく、建設的で、プロフェッショナルな態度で行動することが期待されています。

## 貢献の方法

### バグ報告

バグを発見した場合は、以下の手順で報告してください：

1. [Issues](https://github.com/SilentMalachite/ordina/issues)ページで既存の問題を検索
2. 同様の問題が報告されていない場合は、新しいIssueを作成
3. 以下の情報を含めてください：
   - 環境（OS、PHPバージョンなど）
   - 再現手順
   - 期待される動作
   - 実際の動作
   - エラーメッセージやスクリーンショット（可能な場合）

### 機能提案

新機能の提案は大歓迎です：

1. [Discussions](https://github.com/SilentMalachite/ordina/discussions)で提案を共有
2. 実装前に他の貢献者からのフィードバックを待つ
3. 合意が得られたら、Issueを作成して実装を開始

### プルリクエスト

#### 準備

1. フォークとクローン
```bash
git clone https://github.com/SilentMalachite/ordina.git
cd ordina
```

2. 開発環境のセットアップ
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

3. フィーチャーブランチの作成
```bash
git checkout -b feature/your-feature-name
```

#### 開発ガイドライン

##### コーディング規約

- PSR-12コーディング標準に従う
- Laravel のベストプラクティスを遵守
- 意味のある変数名と関数名を使用
- 必要に応じてコメントを追加（日本語OK）

##### コミットメッセージ

- 明確で簡潔なメッセージを書く
- 日本語または英語どちらでもOK
- 形式: `type: description`

例：
```
feat: 商品検索機能の追加
fix: 在庫数計算のバグ修正
docs: READMEの更新
```

##### テスト

- 新機能にはテストを追加
- 既存のテストがすべてパスすることを確認
```bash
php artisan test
```

##### コードスタイル

- Laravel Pintを使用してコードを整形
```bash
./vendor/bin/pint
```

#### プルリクエストの提出

1. 変更をコミット
```bash
git add .
git commit -m "feat: 新機能の説明"
```

2. フォークにプッシュ
```bash
git push origin feature/your-feature-name
```

3. GitHubでプルリクエストを作成
   - タイトルと説明を明確に記載
   - 関連するIssueがあればリンク
   - スクリーンショットや動画があれば追加

### ドキュメント

- READMEの改善
- 使用方法の追加
- APIドキュメントの更新

## 開発環境

### 必要なツール

- PHP 8.1以上
- Composer
- Node.js 16以上
- Git

### 推奨ツール

- Laravel Herd
- Visual Studio Code
- PHPStorm

## 質問とサポート

- 技術的な質問: [Discussions](https://github.com/SilentMalachite/ordina/discussions)
- バグ報告: [Issues](https://github.com/SilentMalachite/ordina/issues)

## ライセンス

貢献していただいたコードは、プロジェクトと同じMITライセンスのもとで公開されます。

## 謝辞

すべての貢献者に感謝します！あなたの協力がOrdinaをより良いものにします。