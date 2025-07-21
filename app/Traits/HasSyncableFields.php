<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSyncableFields
{
    /**
     * モデルのブート時に呼ばれる
     */
    protected static function bootHasSyncableFields()
    {
        // 作成時にUUIDを自動生成
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $model->is_dirty = true;
        });

        // 更新時にis_dirtyフラグを立てる
        static::updating(function ($model) {
            // 同期関連のフィールドのみが変更された場合はis_dirtyを立てない
            $syncFields = ['last_synced_at', 'is_dirty'];
            $dirtyFields = array_keys($model->getDirty());
            $nonSyncDirtyFields = array_diff($dirtyFields, $syncFields);
            
            if (count($nonSyncDirtyFields) > 0) {
                $model->is_dirty = true;
            }
        });
    }

    /**
     * 同期用のフィールドをfillableに追加
     */
    public function initializeHasSyncableFields()
    {
        $this->fillable = array_merge($this->fillable, [
            'uuid',
            'last_synced_at',
            'is_dirty'
        ]);
    }

    /**
     * 同期完了時に呼ぶメソッド
     */
    public function markAsSynced()
    {
        $this->update([
            'is_dirty' => false,
            'last_synced_at' => now()
        ]);
    }

    /**
     * 未同期のレコードを取得するスコープ
     */
    public function scopeUnsyncedRecords($query)
    {
        return $query->where('is_dirty', true);
    }

    /**
     * 指定日時以降に更新されたレコードを取得するスコープ
     */
    public function scopeUpdatedSince($query, $datetime)
    {
        return $query->where('updated_at', '>', $datetime);
    }
}