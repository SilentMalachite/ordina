# Changelog

このプロジェクトの全ての重要な変更はこのファイルで管理します。
形式は Keep a Changelog に準拠し、バージョニングは Semantic Versioning を採用します。

## [Unreleased]

_TBD_

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
