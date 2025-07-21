<div x-data="onlineStatusIndicator()" 
     x-init="init()"
     class="flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium"
     :class="{
         'bg-green-100 text-green-800': isOnline && !isSyncing,
         'bg-yellow-100 text-yellow-800': isOnline && isSyncing,
         'bg-red-100 text-red-800': !isOnline
     }">
    <span class="w-2 h-2 rounded-full"
          :class="{
              'bg-green-500': isOnline && !isSyncing,
              'bg-yellow-500 animate-pulse': isOnline && isSyncing,
              'bg-red-500': !isOnline
          }">
    </span>
    <span x-text="statusText"></span>
    
    <!-- 同期エラーがある場合の表示 -->
    <template x-if="syncError">
        <span class="text-xs text-red-600" x-text="syncError"></span>
    </template>
</div>

<script>
function onlineStatusIndicator() {
    return {
        isOnline: navigator.onLine,
        isSyncing: false,
        syncError: null,
        serverCheckInterval: null,
        
        get statusText() {
            if (!this.isOnline) return 'オフライン';
            if (this.isSyncing) return '同期中...';
            return 'オンライン';
        },
        
        init() {
            // オンライン/オフラインイベントのリスナー設定
            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());
            
            // 定期的なサーバーチェック（30秒ごと）
            this.serverCheckInterval = setInterval(() => {
                if (this.isOnline) {
                    this.checkServerConnection();
                }
            }, 30000);
            
            // 初回チェック
            if (this.isOnline) {
                this.checkServerConnection();
            }
            
            // クリーンアップ
            this.$el.addEventListener('remove', () => {
                if (this.serverCheckInterval) {
                    clearInterval(this.serverCheckInterval);
                }
            });
        },
        
        async handleOnline() {
            this.isOnline = true;
            // サーバー接続を確認
            const serverAvailable = await this.checkServerConnection();
            if (serverAvailable) {
                // 自動同期を開始
                this.startSync();
            }
        },
        
        handleOffline() {
            this.isOnline = false;
            this.isSyncing = false;
            // オフライン通知を表示
            this.showNotification('オフラインモードに切り替わりました', 'warning');
        },
        
        async checkServerConnection() {
            try {
                const response = await fetch('/api/health-check', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                    // タイムアウト設定
                    signal: AbortSignal.timeout(5000)
                });
                
                return response.ok;
            } catch (error) {
                console.error('Server check failed:', error);
                return false;
            }
        },
        
        async startSync() {
            if (this.isSyncing) return;
            
            this.isSyncing = true;
            this.syncError = null;
            
            try {
                // 同期APIを呼び出し
                const response = await fetch('/api/sync/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showNotification('データ同期が完了しました', 'success');
                    // 同期完了イベントを発火
                    window.dispatchEvent(new CustomEvent('sync-completed', { detail: data }));
                } else {
                    throw new Error(data.message || '同期エラーが発生しました');
                }
            } catch (error) {
                console.error('Sync error:', error);
                this.syncError = error.message;
                this.showNotification('同期エラー: ' + error.message, 'error');
            } finally {
                this.isSyncing = false;
            }
        },
        
        showNotification(message, type) {
            // NativePHPの通知機能を使用
            if (window.Native) {
                window.Native.notification({
                    title: 'Ordina',
                    body: message,
                    icon: type === 'error' ? 'error' : 'info'
                });
            }
            
            // ブラウザ内通知も表示（Alpine.jsのイベントで）
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }
}
</script>