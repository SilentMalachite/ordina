<?php

namespace App\Http\Controllers;

use App\Models\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobStatusController extends Controller
{
    /**
     * ジョブステータス一覧を表示
     */
    public function index()
    {
        $jobStatuses = JobStatus::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('job-statuses.index', compact('jobStatuses'));
    }

    /**
     * 特定のジョブステータスをJSON形式で返す
     */
    public function show(JobStatus $jobStatus)
    {
        // 自分のジョブのみアクセス可能
        if ($jobStatus->user_id !== Auth::id()) {
            abort(403, 'アクセス権限がありません。');
        }

        return response()->json([
            'id' => $jobStatus->id,
            'job_name' => $jobStatus->job_name,
            'status' => $jobStatus->status,
            'progress' => $jobStatus->progress,
            'output' => $jobStatus->output,
            'meta' => $jobStatus->meta,
            'started_at' => $jobStatus->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $jobStatus->completed_at?->format('Y-m-d H:i:s'),
            'is_finished' => $jobStatus->isFinished(),
        ]);
    }
}
