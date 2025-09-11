<?php

namespace App\Http\Controllers;

use App\Services\ClosingService;
use App\Services\ErrorHandlingService;
use App\Models\ClosingDate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClosingController extends Controller
{
    protected $closingService;
    protected $errorService;

    public function __construct()
    {
        $this->middleware('permission:closing-date-manage');
        $this->closingService = new ClosingService();
        $this->errorService = new ErrorHandlingService();
    }

    /**
     * 締め処理画面を表示
     */
    public function index()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return [
                'closing_dates' => $this->closingService->getClosingDates(),
                'next_closing_date' => $this->closingService->getNextClosingDate(),
                'closing_history' => $this->closingService->getClosingHistory()
            ];
        }, '締め処理画面データの取得');

        if ($result['success']) {
            $data = $result['data'];
            return view('closing.index', $data);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 締め処理を実行
     */
    public function process(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'closing_date_id' => 'required|exists:closing_dates,id',
            'closing_date' => 'required|date',
            'confirmation' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->closingService->processClosing(
            $request->closing_date_id,
            Carbon::parse($request->closing_date)
        );

        if ($result['success']) {
            return redirect()->route('closing.index')
                ->with('success', '締め処理が正常に完了しました。');
        }

        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * 締め処理の詳細を表示
     */
    public function show(Request $request)
    {
        // パラメータ未指定時はデフォルト値を採用（最新の締め日／本日）
        $closingDateId = $request->input('closing_date_id')
            ?? ClosingDate::query()->latest('id')->value('id');
        $closingDateStr = $request->input('closing_date')
            ?? now()->toDateString();

        // バリデーション（デフォルト適用後）
        $validator = \Validator::make(
            ['closing_date_id' => $closingDateId, 'closing_date' => $closingDateStr],
            [
                'closing_date_id' => 'required|exists:closing_dates,id',
                'closing_date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $result = $this->closingService->processClosing(
            $closingDateId,
            Carbon::parse($closingDateStr)
        );

        if ($result['success']) {
            $data = $result['data'];
            return view('closing.show', $data);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * 締め処理の履歴を表示
     */
    public function history()
    {
        $result = $this->errorService->safeDatabaseOperation(function() {
            return $this->closingService->getClosingHistory(50);
        }, '締め処理履歴の取得');

        if ($result['success']) {
            $history = $result['data'];
            return view('closing.history', compact('history'));
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
