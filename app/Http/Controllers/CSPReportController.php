<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CSPReportController extends Controller
{
    /**
     * CSP違反レポートを処理
     */
    public function report(Request $request)
    {
        $report = $request->all();

        // CSPレポートをログに記録
        Log::info('CSP Violation Report', [
            'document-uri' => $report['csp-report']['document-uri'] ?? null,
            'violated-directive' => $report['csp-report']['violated-directive'] ?? null,
            'original-policy' => $report['csp-report']['original-policy'] ?? null,
            'blocked-uri' => $report['csp-report']['blocked-uri'] ?? null,
            'source-file' => $report['csp-report']['source-file'] ?? null,
            'line-number' => $report['csp-report']['line-number'] ?? null,
            'column-number' => $report['csp-report']['column-number'] ?? null,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        // CSPレポートは200 OKで応答
        return response()->json(['status' => 'received'], 200);
    }
}

