# Ordina API 仕様書

## 概要

Ordina在庫管理システムのAPI仕様書です。このAPIは在庫管理、取引管理、顧客管理、レポート機能を提供します。

## 認証

### 認証方式
- Laravel Sanctum を使用したトークンベース認証
- セッションベース認証（Web UI用）

### 認証ヘッダー
```
Authorization: Bearer {token}
```

推奨: APIトークンは Bearer ヘッダーで送信してください。
互換性のためクエリ/フォームでの指定も動作しますが、将来的に廃止予定です。

## エンドポイント一覧

### 認証関連

#### ログイン
```
POST /api/login
```

**リクエストボディ:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**レスポンス:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "ユーザー名",
        "email": "user@example.com",
        "is_admin": false
    },
    "token": "1|abcdef..."
}
```

#### ログアウト
```
POST /api/logout
```

**レスポンス:**
```json
{
    "success": true,
    "message": "ログアウトしました"
}
```

### 商品管理

#### 商品一覧取得
```
GET /api/products
```

**クエリパラメータ:**
- `search`: 検索キーワード（商品コード、商品名）
- `page`: ページ番号（デフォルト: 1）
- `per_page`: 1ページあたりの件数（デフォルト: 20）

**レスポンス:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "product_code": "PROD001",
                "name": "商品名",
                "stock_quantity": 100,
                "unit_price": 1000.00,
                "selling_price": 1500.00,
                "description": "商品説明",
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "total": 50,
        "per_page": 20,
        "last_page": 3
    }
}
```

#### 商品詳細取得
```
GET /api/products/{id}
```

**レスポンス:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "product_code": "PROD001",
        "name": "商品名",
        "stock_quantity": 100,
        "unit_price": 1000.00,
        "selling_price": 1500.00,
        "description": "商品説明",
        "transactions": [
            {
                "id": 1,
                "type": "sale",
                "quantity": 5,
                "total_amount": 7500.00,
                "transaction_date": "2024-01-15",
                "customer": {
                    "id": 1,
                    "name": "顧客名"
                }
            }
        ],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 商品作成
```
POST /api/products
```

**リクエストボディ:**
```json
{
    "product_code": "PROD001",
    "name": "商品名",
    "stock_quantity": 100,
    "unit_price": 1000.00,
    "selling_price": 1500.00,
    "description": "商品説明"
}
```

**レスポンス:**
```json
{
    "success": true,
    "message": "商品が正常に作成されました",
    "data": {
        "id": 1,
        "product_code": "PROD001",
        "name": "商品名",
        "stock_quantity": 100,
        "unit_price": 1000.00,
        "selling_price": 1500.00,
        "description": "商品説明",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 商品更新
```
PUT /api/products/{id}
```

**リクエストボディ:**
```json
{
    "product_code": "PROD001",
    "name": "更新された商品名",
    "stock_quantity": 150,
    "unit_price": 1200.00,
    "selling_price": 1800.00,
    "description": "更新された商品説明"
}
```

#### 商品削除
```
DELETE /api/products/{id}
```

**レスポンス:**
```json
{
    "success": true,
    "message": "商品が正常に削除されました"
}
```

### 顧客管理

#### 顧客一覧取得
```
GET /api/customers
```

**クエリパラメータ:**
- `search`: 検索キーワード（顧客名、会社名、メールアドレス）
- `page`: ページ番号
- `per_page`: 1ページあたりの件数

**レスポンス:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "顧客名",
                "email": "customer@example.com",
                "phone": "090-1234-5678",
                "address": "住所",
                "company": "会社名",
                "notes": "備考",
                "total_transactions": 5,
                "transactions_sum_total_amount": 25000.00,
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "total": 20,
        "per_page": 20,
        "last_page": 1
    }
}
```

#### 顧客詳細取得
```
GET /api/customers/{id}
```

#### 顧客作成
```
POST /api/customers
```

**リクエストボディ:**
```json
{
    "name": "顧客名",
    "email": "customer@example.com",
    "phone": "090-1234-5678",
    "address": "住所",
    "company": "会社名",
    "notes": "備考"
}
```

#### 顧客更新
```
PUT /api/customers/{id}
```

#### 顧客削除
```
DELETE /api/customers/{id}
```

### 取引管理

#### 取引一覧取得
```
GET /api/transactions
```

**クエリパラメータ:**
- `type`: 取引タイプ（sale, rental）
- `customer_id`: 顧客ID
- `product_id`: 商品ID
- `date_from`: 開始日（YYYY-MM-DD）
- `date_to`: 終了日（YYYY-MM-DD）
- `page`: ページ番号
- `per_page`: 1ページあたりの件数

**レスポンス:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "type": "sale",
                "quantity": 5,
                "unit_price": 1500.00,
                "total_amount": 7500.00,
                "transaction_date": "2024-01-15",
                "returned_at": null,
                "notes": "取引備考",
                "product": {
                    "id": 1,
                    "product_code": "PROD001",
                    "name": "商品名"
                },
                "customer": {
                    "id": 1,
                    "name": "顧客名",
                    "company": "会社名"
                },
                "user": {
                    "id": 1,
                    "name": "担当者名"
                },
                "created_at": "2024-01-15T00:00:00.000000Z",
                "updated_at": "2024-01-15T00:00:00.000000Z"
            }
        ],
        "total": 100,
        "per_page": 20,
        "last_page": 5
    }
}
```

#### 取引作成
```
POST /api/transactions
```

**リクエストボディ:**
```json
{
    "type": "sale",
    "customer_id": 1,
    "product_id": 1,
    "quantity": 5,
    "unit_price": 1500.00,
    "transaction_date": "2024-01-15",
    "expected_return_date": "2024-01-22",
    "notes": "取引備考"
}
```

#### 取引更新
```
PUT /api/transactions/{id}
```

#### 取引削除
```
DELETE /api/transactions/{id}
```

#### 貸出返却
```
POST /api/transactions/{id}/return
```

**レスポンス:**
```json
{
    "success": true,
    "message": "返却が正常に処理されました"
}
```

### 在庫管理

#### 在庫一覧取得
```
GET /api/inventory
```

**クエリパラメータ:**
- `low_stock_only`: 低在庫のみ（true/false）
- `search`: 検索キーワード

#### 在庫調整
```
POST /api/inventory/adjustments
```

**リクエストボディ:**
```json
{
    "product_id": 1,
    "adjustment_type": "increase",
    "quantity": 10,
    "reason": "入庫",
    "notes": "調整備考"
}
```

### レポート

#### 売上レポート
```
GET /api/reports/sales
```

**クエリパラメータ:**
- `date_from`: 開始日
- `date_to`: 終了日
- `customer_id`: 顧客ID
- `group_by`: グループ化（daily, weekly, monthly）

#### 在庫レポート
```
GET /api/reports/inventory
```

**クエリパラメータ:**
- `low_stock_only`: 低在庫のみ

#### 顧客レポート
```
GET /api/reports/customers
```

**クエリパラメータ:**
- `date_from`: 開始日
- `date_to`: 終了日

### Excel出力

#### 売上レポートExcel出力
```
GET /api/reports/export/sales
```

**クエリパラメータ:**
- `date_from`: 開始日
- `date_to`: 終了日
- `customer_id`: 顧客ID

**レスポンス:**
Excelファイル（.xlsx）

#### 在庫レポートExcel出力
```
GET /api/reports/export/inventory
```

**クエリパラメータ:**
- `low_stock_only`: 低在庫のみ

#### 顧客レポートExcel出力
```
GET /api/reports/export/customers
```

#### 総合レポートExcel出力
```
GET /api/reports/export/comprehensive
```

**クエリパラメータ:**
- `date_from`: 開始日
- `date_to`: 終了日
- `customer_id`: 顧客ID

### 在庫アラート

#### 在庫アラート一覧
```
GET /api/stock-alerts
```

**レスポンス:**
```json
{
    "success": true,
    "data": {
        "low_stock_products": [
            {
                "id": 1,
                "product_code": "PROD001",
                "name": "商品名",
                "stock_quantity": 5,
                "unit_price": 1000.00,
                "selling_price": 1500.00
            }
        ],
        "out_of_stock_products": [],
        "statistics": {
            "total_products": 50,
            "low_stock_products": 3,
            "out_of_stock_products": 0,
            "low_stock_threshold": 10,
            "last_check": "2024-01-15 10:30:00"
        }
    }
}
```

#### 在庫アラート統計
```
GET /api/stock-alerts/statistics
```

#### 手動在庫チェック実行
```
POST /api/stock-alerts/run-check
```

### 締め処理

#### 締め処理一覧
```
GET /api/closing
```

#### 締め処理プレビュー
```
GET /api/closing/show
```

**クエリパラメータ:**
- `closing_date_id`: 締め日ID
- `closing_date`: 締め処理日

#### 締め処理実行
```
POST /api/closing/process
```

**リクエストボディ:**
```json
{
    "closing_date_id": 1,
    "closing_date": "2024-01-31",
    "confirmation": true
}
```

#### 締め処理履歴
```
GET /api/closing/history
```

## エラーレスポンス

### バリデーションエラー
```json
{
    "success": false,
    "error_code": "VALIDATION_ERROR",
    "message": "入力データに問題があります",
    "errors": {
        "name": ["商品名は必須です"],
        "stock_quantity": ["在庫数は0以上である必要があります"]
    }
}
```

### 認証エラー
```json
{
    "success": false,
    "error_code": "UNAUTHENTICATED",
    "message": "認証が必要です"
}
```

### 権限エラー
```json
{
    "success": false,
    "error_code": "PERMISSION_DENIED",
    "message": "この操作を実行する権限がありません"
}
```

### データ未発見エラー
```json
{
    "success": false,
    "error_code": "DATA_NOT_FOUND",
    "message": "指定されたデータが見つかりません"
}
```

### システムエラー
```json
{
    "success": false,
    "error_code": "SYSTEM_ERROR",
    "message": "システムエラーが発生しました"
}
```

## ステータスコード

- `200`: 成功
- `201`: 作成成功
- `400`: リクエストエラー
- `401`: 認証エラー
- `403`: 権限エラー
- `404`: データ未発見
- `422`: バリデーションエラー
- `500`: サーバーエラー

## レート制限

- 認証済みユーザー: 1000リクエスト/時間
- 未認証ユーザー: 100リクエスト/時間

## バージョニング

現在のAPIバージョン: v1

バージョンはURLパスに含まれます:
```
/api/v1/products
```

## サンプルコード

### JavaScript (Fetch API)
```javascript
// 商品一覧取得
const response = await fetch('/api/products', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    }
});
const data = await response.json();

// 商品作成
const newProduct = await fetch('/api/products', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        product_code: 'PROD001',
        name: '新商品',
        stock_quantity: 100,
        unit_price: 1000,
        selling_price: 1500
    })
});
```

### PHP (cURL)
```php
// 商品一覧取得
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://example.com/api/products');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

### Python (requests)
```python
import requests

# 商品一覧取得
headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}
response = requests.get('https://example.com/api/products', headers=headers)
data = response.json()

# 商品作成
product_data = {
    'product_code': 'PROD001',
    'name': '新商品',
    'stock_quantity': 100,
    'unit_price': 1000,
    'selling_price': 1500
}
response = requests.post('https://example.com/api/products', 
                        headers=headers, json=product_data)
```
