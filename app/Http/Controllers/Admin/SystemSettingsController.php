<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClosingDate;
use App\Services\ErrorHandlingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemSettingsController extends Controller
{
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:system-manage');
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * システム設定画面を表示
     */
    public function index()
    {
        $settings = [
            'low_stock_threshold' => 10, // 設定値として保存したい場合は設定テーブルを作成
            'default_closing_day' => 25,
            'backup_frequency' => 'weekly',
        ];

        return view('admin.system-settings', compact('settings'));
    }

    /**
     * 締め日一覧を表示
     */
    public function closingDates()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return ClosingDate::orderBy('day_of_month', 'desc')->paginate(20);
        }, '締め日一覧の取得');

        if ($result['success']) {
            $closingDates = $result['data'];
            return view('admin.closing-dates', compact('closingDates'));
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 締め日作成フォームを表示
     */
    public function createClosingDate()
    {
        return view('admin.create-closing-date');
    }

    /**
     * 締め日を保存
     */
    public function storeClosingDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_month' => 'required|integer|between:1,31|unique:closing_dates',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->errorService->safeDatabaseOperation(function() use ($request) {
            return ClosingDate::create([
                'day_of_month' => $request->day_of_month,
                'description' => $request->description,
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);
        }, '締め日の作成');

        if ($result['success']) {
            return redirect()->route('admin.closing-dates')
                ->with('success', '締め日が正常に設定されました。');
        }

        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * 締め日を削除
     */
    public function destroyClosingDate(ClosingDate $closingDate)
    {
        $result = $this->errorService->safeDatabaseOperation(function() use ($closingDate) {
            $closingDate->delete();
            return true;
        }, '締め日の削除');

        if ($result['success']) {
            return redirect()->route('admin.closing-dates')
                ->with('success', '締め日が正常に削除されました。');
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * システムログを表示
     */
    public function systemLogs()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            $logPath = storage_path('logs/laravel.log');
            $logs = [];
            
            if (file_exists($logPath)) {
                $logContent = file_get_contents($logPath);
                $logLines = array_reverse(explode("\n", $logContent));
                $logs = array_slice($logLines, 0, 100); // 最新100行
            }

            return $logs;
        }, 'システムログの取得');

        if ($result['success']) {
            $logs = $result['data'];
            return view('admin.system-logs', compact('logs'));
        }

        return redirect()->back()->with('error', $result['message']);
    }
}