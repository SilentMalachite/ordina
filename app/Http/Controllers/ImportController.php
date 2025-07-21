<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportProductsFromCsv;
use App\Jobs\ImportCustomersFromCsv;
use App\Jobs\ImportTransactionsFromCsv;
use Carbon\Carbon;

class ImportController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:import-run');
    }

    public function index()
    {
        return view('import.index');
    }

    public function products()
    {
        return view('import.products');
    }

    public function customers()
    {
        return view('import.customers');
    }

    public function transactions()
    {
        return view('import.transactions');
    }

    public function importProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'has_header' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $hasHeader = $request->boolean('has_header', true);
            
            // ファイルを一時保存
            $path = $file->store('imports');
            
            // ジョブをキューに投入
            ImportProductsFromCsv::dispatch($path, auth()->id(), $hasHeader);
            
            return redirect()->back()->with('success', 
                '商品インポート処理を開始しました。完了後、デスクトップ通知でお知らせします。'
            );
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ファイルの処理中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    public function importCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'has_header' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $hasHeader = $request->boolean('has_header', true);
            
            // ファイルを一時保存
            $path = 'imports/customers_' . uniqid() . '.csv';
            $file->storeAs('', $path, 'local');
            
            // ジョブをキューに追加
            ImportCustomersFromCsv::dispatch($path, Auth::id(), $hasHeader);
            
            return redirect()->back()->with('success', 
                '顧客インポートジョブをキューに追加しました。ジョブ状況ページで進捗をご確認ください。'
            );
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ファイルの処理中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    public function importTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'has_header' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $hasHeader = $request->boolean('has_header', true);
            
            // ファイルを一時保存
            $path = 'imports/transactions_' . uniqid() . '.csv';
            $file->storeAs('', $path, 'local');
            
            // ジョブをキューに追加
            ImportTransactionsFromCsv::dispatch($path, Auth::id(), $hasHeader);
            
            return redirect()->back()->with('success', 
                '取引インポートジョブをキューに追加しました。ジョブ状況ページで進捗をご確認ください。'
            );
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ファイルの処理中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    private function parseCsvFile($file, $hasHeader = true)
    {
        $path = $file->getRealPath();
        $data = [];
        
        if (($handle = fopen($path, 'r')) !== false) {
            $lineNumber = 0;
            $headers = null;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNumber++;
                
                if ($hasHeader && $lineNumber === 1) {
                    $headers = $row;
                    continue;
                }
                
                if ($hasHeader && $headers) {
                    $data[] = array_combine($headers, $row);
                } else {
                    $data[] = $row;
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    private function processProductImport($data)
    {
        $success = 0;
        $errors = 0;
        
        DB::transaction(function() use ($data, &$success, &$errors) {
            foreach ($data as $row) {
                try {
                    $productData = $this->mapProductData($row);
                    
                    $validator = Validator::make($productData, [
                        'product_code' => 'required|string|max:50|unique:products',
                        'name' => 'required|string|max:255',
                        'stock_quantity' => 'required|integer|min:0',
                        'unit_price' => 'required|numeric|min:0',
                        'selling_price' => 'required|numeric|min:0',
                    ]);
                    
                    if ($validator->fails()) {
                        $errors++;
                        continue;
                    }
                    
                    Product::create($productData);
                    $success++;
                    
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        });
        
        return ['success' => $success, 'errors' => $errors];
    }

    private function processCustomerImport($data)
    {
        $success = 0;
        $errors = 0;
        
        DB::transaction(function() use ($data, &$success, &$errors) {
            foreach ($data as $row) {
                try {
                    $customerData = $this->mapCustomerData($row);
                    
                    $validator = Validator::make($customerData, [
                        'name' => 'required|string|max:255',
                        'type' => 'required|in:individual,company',
                        'email' => 'nullable|email|max:255',
                        'phone' => 'nullable|string|max:20',
                    ]);
                    
                    if ($validator->fails()) {
                        $errors++;
                        continue;
                    }
                    
                    Customer::create($customerData);
                    $success++;
                    
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        });
        
        return ['success' => $success, 'errors' => $errors];
    }

    private function processTransactionImport($data)
    {
        $success = 0;
        $errors = 0;
        
        DB::transaction(function() use ($data, &$success, &$errors) {
            foreach ($data as $row) {
                try {
                    $transactionData = $this->mapTransactionData($row);
                    
                    $validator = Validator::make($transactionData, [
                        'type' => 'required|in:sale,rental',
                        'customer_id' => 'required|exists:customers,id',
                        'product_id' => 'required|exists:products,id',
                        'quantity' => 'required|integer|min:1',
                        'unit_price' => 'required|numeric|min:0',
                        'transaction_date' => 'required|date',
                    ]);
                    
                    if ($validator->fails()) {
                        $errors++;
                        continue;
                    }
                    
                    $product = Product::find($transactionData['product_id']);
                    if ($product->stock_quantity < $transactionData['quantity']) {
                        $errors++;
                        continue;
                    }
                    
                    $transactionData['user_id'] = auth()->id();
                    $transactionData['total_amount'] = $transactionData['quantity'] * $transactionData['unit_price'];
                    
                    Transaction::create($transactionData);
                    $product->decrement('stock_quantity', $transactionData['quantity']);
                    
                    $success++;
                    
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        });
        
        return ['success' => $success, 'errors' => $errors];
    }

    private function mapProductData($row)
    {
        if (is_array($row) && isset($row[0])) {
            return [
                'product_code' => $row[0] ?? '',
                'name' => $row[1] ?? '',
                'stock_quantity' => (int) ($row[2] ?? 0),
                'unit_price' => (float) ($row[3] ?? 0),
                'selling_price' => (float) ($row[4] ?? 0),
                'description' => $row[5] ?? '',
            ];
        }
        
        return [
            'product_code' => $row['商品コード'] ?? $row['product_code'] ?? '',
            'name' => $row['商品名'] ?? $row['name'] ?? '',
            'stock_quantity' => (int) ($row['在庫数'] ?? $row['stock_quantity'] ?? 0),
            'unit_price' => (float) ($row['単価'] ?? $row['unit_price'] ?? 0),
            'selling_price' => (float) ($row['売値'] ?? $row['selling_price'] ?? 0),
            'description' => $row['説明'] ?? $row['description'] ?? '',
        ];
    }

    private function mapCustomerData($row)
    {
        if (is_array($row) && isset($row[0])) {
            return [
                'name' => $row[0] ?? '',
                'type' => $row[1] ?? 'individual',
                'email' => $row[2] ?? '',
                'phone' => $row[3] ?? '',
                'address' => $row[4] ?? '',
                'contact_person' => $row[5] ?? '',
                'notes' => $row[6] ?? '',
            ];
        }
        
        return [
            'name' => $row['顧客名'] ?? $row['name'] ?? '',
            'type' => $row['タイプ'] ?? $row['type'] ?? 'individual',
            'email' => $row['メールアドレス'] ?? $row['email'] ?? '',
            'phone' => $row['電話番号'] ?? $row['phone'] ?? '',
            'address' => $row['住所'] ?? $row['address'] ?? '',
            'contact_person' => $row['担当者'] ?? $row['contact_person'] ?? '',
            'notes' => $row['備考'] ?? $row['notes'] ?? '',
        ];
    }

    private function mapTransactionData($row)
    {
        if (is_array($row) && isset($row[0])) {
            $customer = Customer::where('name', $row[1])->first();
            $product = Product::where('product_code', $row[2])->orWhere('name', $row[2])->first();
            
            return [
                'type' => $row[0] === '売上' ? 'sale' : 'rental',
                'customer_id' => $customer ? $customer->id : null,
                'product_id' => $product ? $product->id : null,
                'quantity' => (int) ($row[3] ?? 1),
                'unit_price' => (float) ($row[4] ?? 0),
                'transaction_date' => Carbon::parse($row[5] ?? now())->format('Y-m-d'),
                'notes' => $row[6] ?? '',
            ];
        }
        
        $customer = Customer::where('name', $row['顧客名'] ?? $row['customer_name'])->first();
        $product = Product::where('product_code', $row['商品コード'] ?? $row['product_code'])
            ->orWhere('name', $row['商品名'] ?? $row['product_name'])->first();
        
        return [
            'type' => ($row['取引タイプ'] ?? $row['type']) === '売上' ? 'sale' : 'rental',
            'customer_id' => $customer ? $customer->id : null,
            'product_id' => $product ? $product->id : null,
            'quantity' => (int) ($row['数量'] ?? $row['quantity'] ?? 1),
            'unit_price' => (float) ($row['単価'] ?? $row['unit_price'] ?? 0),
            'transaction_date' => Carbon::parse($row['取引日'] ?? $row['transaction_date'] ?? now())->format('Y-m-d'),
            'notes' => $row['備考'] ?? $row['notes'] ?? '',
        ];
    }

    public function downloadTemplate($type)
    {
        switch ($type) {
            case 'products':
                return $this->generateProductTemplate();
            case 'customers':
                return $this->generateCustomerTemplate();
            case 'transactions':
                return $this->generateTransactionTemplate();
            default:
                return redirect()->back()->with('error', '無効なテンプレートタイプです。');
        }
    }

    private function generateProductTemplate()
    {
        $headers = ['商品コード', '商品名', '在庫数', '単価', '売値', '説明'];
        
        return $this->generateCsvTemplate($headers, 'product_template.csv');
    }

    private function generateCustomerTemplate()
    {
        $headers = ['顧客名', 'タイプ', 'メールアドレス', '電話番号', '住所', '担当者', '備考'];
        
        return $this->generateCsvTemplate($headers, 'customer_template.csv');
    }

    private function generateTransactionTemplate()
    {
        $headers = ['取引タイプ', '顧客名', '商品コード', '数量', '単価', '取引日', '備考'];
        
        return $this->generateCsvTemplate($headers, 'transaction_template.csv');
    }

    private function generateCsvTemplate($headers, $filename)
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($csv));
    }
}