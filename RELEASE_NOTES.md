# Ordina v0.1.0 (2025-09-04)

本リリースでは、GitHub上でのコラボレーションを円滑にするためのドキュメント／テンプレート整備と、READMEの開発手順を拡充しました。

## 変更概要

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

## アップグレードメモ
- 破壊的変更はありません。
- ドキュメント整備のみのため、アプリ動作に影響はありません。

## 謝辞
- コミュニティの皆さまのフィードバックに感謝します！
