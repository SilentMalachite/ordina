<!-- 同期競合解決モーダル -->
<div x-data="syncConflictModal()" 
     x-show="showModal" 
     x-init="init()"
     @sync-conflict.window="handleConflict($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- 背景のオーバーレイ -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeModal()"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
        </div>

        <!-- モーダルコンテンツ -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            データの競合が検出されました
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                サーバーとローカルで同じデータが異なる値に更新されています。
                                どちらのデータを使用するか選択してください。
                            </p>
                        </div>
                        
                        <div class="mt-4" x-show="conflict">
                            <div class="space-y-4">
                                <!-- ローカルデータ -->
                                <div class="border rounded-lg p-4 cursor-pointer hover:bg-blue-50 transition-colors"
                                     :class="{'bg-blue-50 border-blue-500': selectedResolution === 'local'}"
                                     @click="selectedResolution = 'local'">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-sm">ローカルの変更（あなたの変更）</h4>
                                        <input type="radio" name="resolution" value="local" 
                                               x-model="selectedResolution"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <div x-html="formatData(conflict.local_data)"></div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            更新日時: <span x-text="formatDate(conflict.local_data.updated_at)"></span>
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- サーバーデータ -->
                                <div class="border rounded-lg p-4 cursor-pointer hover:bg-green-50 transition-colors"
                                     :class="{'bg-green-50 border-green-500': selectedResolution === 'server'}"
                                     @click="selectedResolution = 'server'">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-sm">サーバーの変更（他のユーザーの変更）</h4>
                                        <input type="radio" name="resolution" value="server" 
                                               x-model="selectedResolution"
                                               class="h-4 w-4 text-green-600 focus:ring-green-500">
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <div x-html="formatData(conflict.server_data)"></div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            更新日時: <span x-text="formatDate(conflict.server_data.updated_at)"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                        @click="resolveConflict()"
                        :disabled="!selectedResolution"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    選択したデータを使用
                </button>
                <button type="button"
                        @click="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    後で解決
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function syncConflictModal() {
    return {
        showModal: false,
        conflict: null,
        selectedResolution: null,
        
        init() {
            // ページロード時に保存された競合をチェック
            this.checkSavedConflicts();
        },
        
        handleConflict(conflictData) {
            this.conflict = conflictData;
            this.selectedResolution = null;
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            // 未解決の競合を保存
            if (this.conflict && !this.selectedResolution) {
                this.saveConflict(this.conflict);
            }
        },
        
        async resolveConflict() {
            if (!this.selectedResolution || !this.conflict) return;
            
            try {
                const response = await fetch('/api/sync/resolve-conflict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        conflict: this.conflict,
                        resolution: this.selectedResolution
                    })
                });
                
                if (response.ok) {
                    // 成功通知
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { 
                            message: '競合が解決されました', 
                            type: 'success' 
                        }
                    }));
                    
                    this.removeConflict(this.conflict);
                    this.showModal = false;
                } else {
                    throw new Error('競合の解決に失敗しました');
                }
            } catch (error) {
                console.error('Conflict resolution error:', error);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { 
                        message: 'エラー: ' + error.message, 
                        type: 'error' 
                    }
                }));
            }
        },
        
        formatData(data) {
            // 主要なフィールドのみを表示
            const fields = ['name', 'product_code', 'stock_quantity', 'unit_price', 'selling_price'];
            let html = '<dl class="grid grid-cols-2 gap-x-4 gap-y-2">';
            
            for (const field of fields) {
                if (data[field] !== undefined) {
                    html += `
                        <dt class="text-xs text-gray-500">${this.getFieldLabel(field)}:</dt>
                        <dd class="text-xs font-medium">${data[field]}</dd>
                    `;
                }
            }
            
            html += '</dl>';
            return html;
        },
        
        getFieldLabel(field) {
            const labels = {
                name: '商品名',
                product_code: '商品コード',
                stock_quantity: '在庫数',
                unit_price: '単価',
                selling_price: '売値'
            };
            return labels[field] || field;
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleString('ja-JP');
        },
        
        saveConflict(conflict) {
            const conflicts = JSON.parse(localStorage.getItem('syncConflicts') || '[]');
            conflicts.push(conflict);
            localStorage.setItem('syncConflicts', JSON.stringify(conflicts));
        },
        
        removeConflict(conflict) {
            let conflicts = JSON.parse(localStorage.getItem('syncConflicts') || '[]');
            conflicts = conflicts.filter(c => c.uuid !== conflict.uuid);
            localStorage.setItem('syncConflicts', JSON.stringify(conflicts));
        },
        
        checkSavedConflicts() {
            const conflicts = JSON.parse(localStorage.getItem('syncConflicts') || '[]');
            if (conflicts.length > 0) {
                // 最初の競合を表示
                setTimeout(() => {
                    this.handleConflict(conflicts[0]);
                }, 1000);
            }
        }
    }
}
</script>