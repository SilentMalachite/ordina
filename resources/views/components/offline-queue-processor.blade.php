<!-- オフラインキュープロセッサー -->
<div x-data="offlineQueueProcessor()" 
     x-init="init()"
     @online.window="processQueue()"
     style="display: none;">
</div>

<script>
function offlineQueueProcessor() {
    return {
        isProcessing: false,
        
        init() {
            // ページロード時にオンラインならキューを処理
            if (navigator.onLine) {
                setTimeout(() => this.processQueue(), 2000);
            }
        },
        
        async processQueue() {
            if (this.isProcessing) return;
            
            const queue = JSON.parse(localStorage.getItem('offlineQueue') || '[]');
            if (queue.length === 0) return;
            
            this.isProcessing = true;
            console.log(`Processing ${queue.length} offline requests...`);
            
            const processed = [];
            const failed = [];
            
            for (const request of queue) {
                try {
                    const response = await this.sendRequest(request);
                    if (response.ok) {
                        processed.push(request);
                    } else {
                        failed.push(request);
                        console.error('Failed to process offline request:', response.status);
                    }
                } catch (error) {
                    failed.push(request);
                    console.error('Error processing offline request:', error);
                }
            }
            
            // 処理済みのリクエストを削除
            if (processed.length > 0) {
                const remainingQueue = queue.filter(req => !processed.includes(req));
                localStorage.setItem('offlineQueue', JSON.stringify(remainingQueue));
                
                // 成功通知
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { 
                        message: `${processed.length}件のオフライン変更が同期されました`, 
                        type: 'success' 
                    }
                }));
            }
            
            // 失敗したリクエストがある場合は警告
            if (failed.length > 0) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { 
                        message: `${failed.length}件の変更の同期に失敗しました`, 
                        type: 'warning' 
                    }
                }));
            }
            
            this.isProcessing = false;
            
            // 同期完了後、サーバーから最新データを取得
            if (processed.length > 0) {
                this.triggerServerSync();
            }
        },
        
        async sendRequest(request) {
            const { url, method, data } = request;
            
            // FormDataを再構築
            const formData = new FormData();
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }
            
            return await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        },
        
        triggerServerSync() {
            // サーバー同期イベントを発火
            window.dispatchEvent(new CustomEvent('trigger-sync', {
                detail: { direction: 'pull' }
            }));
        }
    }
}
</script>