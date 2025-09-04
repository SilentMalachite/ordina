name: バグ報告
description: 不具合の報告はこちら
labels: [bug]
body:
  - type: textarea
    id: summary
    attributes:
      label: 概要
      description: どのような不具合かを簡潔に説明してください。
      placeholder: 例）在庫調整画面で保存に失敗する
    validations:
      required: true
  - type: textarea
    id: steps
    attributes:
      label: 再現手順
      description: 不具合を再現するための手順を具体的に記載してください。
      placeholder: |
        1. ○○を開く
        2. ××を入力
        3. 保存をクリック
    validations:
      required: true
  - type: textarea
    id: expected
    attributes:
      label: 期待される動作
    validations:
      required: true
  - type: textarea
    id: actual
    attributes:
      label: 実際の動作
    validations:
      required: true
  - type: input
    id: env
    attributes:
      label: 環境
      description: OS / アプリのバージョン / PHP など
      placeholder: Windows11, Ordina v1.2.3, PHP 8.2
  - type: textarea
    id: logs
    attributes:
      label: ログ / スクリーンショット
      description: 可能であれば貼り付けてください

