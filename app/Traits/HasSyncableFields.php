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
            // 明示的に指定されていない場合のみ未同期扱いにする
            if (!array_key_exists('is_dirty', $model->getAttributes())) {
                $model->is_dirty = true;
            }
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
        $this->fillable = array_merge($this->fillable ?? [], [
            'uuid',
            'last_synced_at',
            'is_dirty',
            // テストおよびユースケースで更新時刻を明示的に変更できるようにする
            'updated_at',
        ]);

        // 型キャストを追加（boolean / datetime）
        $this->casts = array_merge($this->casts ?? [], [
            'is_dirty' => 'boolean',
            'last_synced_at' => 'datetime',
        ]);
    }

    /**
     * 同期完了時に呼ぶメソッド
     */
    public function markAsSynced()
    {
        // モデル属性を更新し、イベント・updated_atを触らずに保存
        $this->is_dirty = false;
        $this->last_synced_at = now();

        $originalTimestamps = $this->timestamps;
        $this->timestamps = false; // updated_at を変更しない
        $this->saveQuietly();      // イベント発火なしで保存し、original を同期
        $this->timestamps = $originalTimestamps;
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
