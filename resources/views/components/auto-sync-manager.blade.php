<!-- 自動同期マネージャー -->
<div x-data="autoSyncManager()" 
     x-init="init()"
     @online.window="startAutoSync()"
     @offline.window="stopAutoSync()"
     style="display: none;">
</div>

<script>
function autoSyncManager() {
    return {
        syncInterval: null,
        isOnline: navigator.onLine,
        syncIntervalMinutes: {{ config('sync.auto_sync_interval', 300) / 60 }},
        lastSyncTime: null,
        
        init() {
            // 初期状態でオンラインなら自動同期を開始
            if (this.isOnline) {
                this.startAutoSync();
            }
            
            // 同期イベントをリッスン
            window.addEventListener('sync-completed', (event) => {
                this.lastSyncTime = new Date();
                console.log('Sync completed at:', this.lastSyncTime);
            });
        },
        
        startAutoSync() {
            if (this.syncInterval) return;
            
            console.log(`Starting auto-sync every ${this.syncIntervalMinutes} minutes`);
            
            // 即座に一度同期を実行
            this.performSync();
            
            // 定期的な同期を設定
            this.syncInterval = setInterval(() => {
                this.performSync();
            }, this.syncIntervalMinutes * 60 * 1000);
        },
        
        stopAutoSync() {
            if (this.syncInterval) {
                console.log('Stopping auto-sync');
                clearInterval(this.syncInterval);
                this.syncInterval = null;
            }
        },
        
        async performSync() {
            console.log('Performing automatic sync...');
            
            try {
                // 同期APIを呼び出し
                const response = await fetch('/api/sync/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mode: 'both' // push and pull
                    })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('Auto-sync initiated:', result);
                    
                    // 同期完了イベントを発火
                    window.dispatchEvent(new CustomEvent('sync-completed', {
                        detail: result
                    }));
                    
                    // ステータスを更新（必要に応じて）
                    if (result.push_data.status === 'syncing' || result.pull_data.status === 'syncing') {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { 
                                message: '自動同期を実行中...', 
                                type: 'info' 
                            }
                        }));
                    }
                } else {
                    throw new Error('同期リクエストが失敗しました');
                }
            } catch (error) {
                console.error('Auto-sync error:', error);
                
                // エラー通知（頻繁すぎる場合は抑制する）
                if (!this.lastErrorTime || (Date.now() - this.lastErrorTime > 300000)) { // 5分以上経過
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { 
                            message: '自動同期でエラーが発生しました', 
                            type: 'error' 
                        }
                    }));
                    this.lastErrorTime = Date.now();
                }
            }
        },
        
        // 手動同期トリガー（他のコンポーネントから呼び出し可能）
        triggerManualSync() {
            console.log('Manual sync triggered');
            this.performSync();
        }
    }
}
</script>