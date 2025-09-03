<?php

namespace App\Http\Controllers;

use App\Models\SyncConflict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyncConflictController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:sync-conflicts-view')->only(['index', 'show']);
        $this->middleware('permission:sync-conflicts-resolve')->only(['resolve', 'ignore']);
    }

    /**
     * 競合一覧を表示
     */
    public function index(Request $request)
    {
        $query = SyncConflict::with('resolver');

        // ステータスでフィルタリング
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        } else {
            // デフォルトで保留中の競合のみ表示
            $query->pending();
        }

        // テーブル名でフィルタリング
        if ($request->has('table') && $request->table !== '') {
            $query->where('table_name', $request->table);
        }

        $conflicts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('sync-conflicts.index', compact('conflicts'));
    }

    /**
     * 競合詳細を表示
     */
    public function show(SyncConflict $syncConflict)
    {
        $differences = $syncConflict->getDifferences();

        return view('sync-conflicts.show', compact('syncConflict', 'differences'));
    }

    /**
     * 競合を解決
     */
    public function resolve(Request $request, SyncConflict $syncConflict)
    {
        $request->validate([
            'resolution_strategy' => 'required|in:local_wins,server_wins,merge',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $userId = Auth::id();

            // 解決戦略に応じた処理
            $this->applyResolutionStrategy($syncConflict, $request->resolution_strategy, $userId);

            // 競合を解決済みにマーク
            $syncConflict->resolve($request->resolution_strategy, $userId);

            Log::info('Sync conflict resolved', [
                'conflict_id' => $syncConflict->id,
                'strategy' => $request->resolution_strategy,
                'user_id' => $userId,
            ]);

            return redirect()->route('sync-conflicts.index')
                ->with('success', '競合が解決されました。');

        } catch (\Exception $e) {
            Log::error('Failed to resolve sync conflict', [
                'conflict_id' => $syncConflict->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '競合の解決中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 競合を無視
     */
    public function ignore(SyncConflict $syncConflict)
    {
        try {
            $userId = Auth::id();

            $syncConflict->ignore($userId);

            Log::info('Sync conflict ignored', [
                'conflict_id' => $syncConflict->id,
                'user_id' => $userId,
            ]);

            return redirect()->route('sync-conflicts.index')
                ->with('success', '競合を無視しました。');

        } catch (\Exception $e) {
            Log::error('Failed to ignore sync conflict', [
                'conflict_id' => $syncConflict->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '競合の無視中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 解決戦略を適用
     */
    private function applyResolutionStrategy(SyncConflict $conflict, string $strategy, int $userId): void
    {
        $modelClass = $this->getModelClassForTable($conflict->table_name);

        if (!$modelClass) {
            throw new \Exception('Invalid table name: ' . $conflict->table_name);
        }

        $record = $modelClass::where('uuid', $conflict->record_uuid)->first();

        if (!$record) {
            throw new \Exception('Record not found for UUID: ' . $conflict->record_uuid);
        }

        switch ($strategy) {
            case 'local_wins':
                // ローカルのデータを保持（何もしない）
                $record->markAsSynced();
                break;

            case 'server_wins':
                // サーバーのデータを適用
                $fillableData = array_intersect_key(
                    $conflict->server_data,
                    array_flip($record->getFillable())
                );
                $record->update($fillableData);
                $record->markAsSynced();
                break;

            case 'merge':
                // マージ処理（実装は要件による）
                $this->mergeConflictData($record, $conflict);
                break;

            default:
                throw new \Exception('Invalid resolution strategy: ' . $strategy);
        }
    }

    /**
     * 競合データをマージ
     */
    private function mergeConflictData($record, SyncConflict $conflict): void
    {
        // 基本的なマージ戦略：nullでない値を優先
        $mergedData = [];

        foreach ($conflict->local_data as $key => $localValue) {
            $serverValue = $conflict->server_data[$key] ?? null;

            if ($localValue !== null && $serverValue === null) {
                $mergedData[$key] = $localValue;
            } elseif ($localValue === null && $serverValue !== null) {
                $mergedData[$key] = $serverValue;
            } elseif ($localValue !== null && $serverValue !== null) {
                // 両方ある場合はローカルを優先（カスタマイズ可能）
                $mergedData[$key] = $localValue;
            }
        }

        // サーバーにしかないフィールドを追加
        foreach ($conflict->server_data as $key => $serverValue) {
            if (!array_key_exists($key, $conflict->local_data) && $serverValue !== null) {
                $mergedData[$key] = $serverValue;
            }
        }

        $fillableData = array_intersect_key($mergedData, array_flip($record->getFillable()));
        $record->update($fillableData);
        $record->markAsSynced();
    }

    /**
     * テーブル名からモデルクラスを取得
     */
    private function getModelClassForTable(string $table): ?string
    {
        return match ($table) {
            'products' => \App\Models\Product::class,
            'customers' => \App\Models\Customer::class,
            'transactions' => \App\Models\Transaction::class,
            'inventory_adjustments' => \App\Models\InventoryAdjustment::class,
            default => null,
        };
    }
}
