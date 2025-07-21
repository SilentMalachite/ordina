<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ジョブステータス
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold mb-4">非同期処理の状況</h3>
                    </div>

                    @if($jobStatuses->isEmpty())
                        <p class="text-gray-500">現在実行中のジョブはありません。</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ジョブ名</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">進捗</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始日時</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">完了日時</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">結果</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($jobStatuses as $jobStatus)
                                        <tr 
                                            x-data="jobStatusRow({{ $jobStatus->id }}, '{{ $jobStatus->status }}', {{ $jobStatus->progress }})"
                                            x-init="initPolling()"
                                        >
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $jobStatus->job_name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span 
                                                    x-text="getStatusLabel(status)"
                                                    :class="getStatusClass(status)"
                                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                                ></span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div 
                                                        class="bg-blue-600 h-2.5 rounded-full transition-all duration-500 ease-out"
                                                        :style="`width: ${progress}%`"
                                                    ></div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1" x-text="`${progress}%`"></p>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $jobStatus->started_at ? $jobStatus->started_at->format('Y/m/d H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="completedAt || '-'">
                                                {{ $jobStatus->completed_at ? $jobStatus->completed_at->format('Y/m/d H:i') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900" x-text="output || '-'">
                                                {{ $jobStatus->output ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $jobStatuses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function jobStatusRow(jobId, initialStatus, initialProgress) {
            return {
                jobId: jobId,
                status: initialStatus,
                progress: initialProgress,
                output: null,
                completedAt: null,
                pollingInterval: null,

                initPolling() {
                    // 既に完了していたらポーリングしない
                    if (this.isFinished()) {
                        return;
                    }

                    // 3秒ごとにポーリング
                    this.pollingInterval = setInterval(() => {
                        this.fetchStatus();
                    }, 3000);

                    // 初回実行
                    this.fetchStatus();
                },

                async fetchStatus() {
                    try {
                        const response = await fetch(`/job-statuses/${this.jobId}`);
                        if (!response.ok) {
                            throw new Error('Failed to fetch job status');
                        }

                        const data = await response.json();
                        
                        // データを更新
                        this.status = data.status;
                        this.progress = data.progress;
                        this.output = data.output;
                        this.completedAt = data.completed_at ? new Date(data.completed_at).toLocaleString('ja-JP') : null;

                        // 完了したらポーリングを停止
                        if (data.is_finished) {
                            clearInterval(this.pollingInterval);
                        }
                    } catch (error) {
                        console.error('Error fetching job status:', error);
                    }
                },

                isFinished() {
                    return ['completed', 'failed'].includes(this.status);
                },

                getStatusLabel(status) {
                    const labels = {
                        'pending': '待機中',
                        'processing': '処理中',
                        'completed': '完了',
                        'failed': '失敗'
                    };
                    return labels[status] || status;
                },

                getStatusClass(status) {
                    const classes = {
                        'pending': 'bg-gray-100 text-gray-800',
                        'processing': 'bg-blue-100 text-blue-800',
                        'completed': 'bg-green-100 text-green-800',
                        'failed': 'bg-red-100 text-red-800'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                }
            }
        }
    </script>
</x-app-layout>