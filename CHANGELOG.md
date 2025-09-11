# Changelog

このプロジェクトの全ての重要な変更はこのファイルで管理します。
形式は Keep a Changelog に準拠し、バージョニングは Semantic Versioning を採用します。

## [Unreleased]

- テスト環境での NativePHP 通知を無効化（`NATIVEPHP_ENABLED` 導入）
- 在庫調整APIのルート修正（`POST /inventory/adjustments` を追加）
- `ClosingService::getClosingDates()` の返り値型（`array`）を厳密化
- 権限ミドルウェアのテスト専用バイパス（admin配下は除外）
- CI/Release 用 GitHub Actions 追加

## [0.2.0] - 2025-09-11

### Fixed
- テスト時に NativePHP 通知で外部接続が発生し失敗する問題を解消
- 在庫不足時のバリデーションを改善し、エラーバッグに `quantity` を追加
- 一部テストの互換性対応（`viewData()` 互換メソッド）

### Added
- `config/nativephp.php` と `NATIVEPHP_ENABLED` を追加
- `app/Http/Middleware/BypassPermissionMiddleware.php` を追加
- GitHub Actions: `ci.yml` / `release.yml`

### Changed
- README にテスト時の通知無効化と環境変数の説明を追記

## [0.1.0] - 2025-09-04

### Added
- Pull Request テンプレートを追加 (`.github/pull_request_template.md`)
- Issue テンプレートを追加（バグ/機能要望/ドキュメント）(`.github/ISSUE_TEMPLATE/*`)
- 行動規範を追加 (`CODE_OF_CONDUCT.md`)
- セキュリティポリシーを追加 (`.github/SECURITY.md`)
- 変更履歴ファイルの雛形を追加 (`CHANGELOG.md`)

### Changed
- README にフロントエンドの開発・ビルド手順（Vite: `npm run dev` / `npm run build`）と NativePHP 併用の注意を追記

### Security
- 脆弱性報告の受け付け方法を明文化（公開Issueではなく私信での連絡を推奨）。実装方針の詳細は `docs/Security.md` を参照
