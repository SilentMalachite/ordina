name: ドキュメント更新
description: ドキュメントの修正・追記
labels: [documentation]
body:
  - type: textarea
    id: scope
    attributes:
      label: 対象
      placeholder: README / docs/InstallationGuide.md など
    validations:
      required: true
  - type: textarea
    id: changes
    attributes:
      label: 変更内容
      description: 何をどう変えるか
    validations:
      required: true
  - type: textarea
    id: reason
    attributes:
      label: 理由
      description: なぜ必要か（不整合の解消、仕様変更の反映など）

