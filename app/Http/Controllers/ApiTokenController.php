<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiTokenController extends Controller
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('permission:api-token-view')->only(['index', 'show']);
        $this->middleware('permission:api-token-create')->only(['create', 'store']);
        $this->middleware('permission:api-token-edit')->only(['edit', 'update']);
        $this->middleware('permission:api-token-delete')->only(['destroy', 'revoke', 'regenerate']);
    }

    /**
     * APIトークンの一覧を表示
     */
    public function index(Request $request)
    {
        $query = ApiToken::with('user');

        // ステータスでフィルタリング
        if ($request->has('status') && $request->status !== '') {
            switch ($request->status) {
                case 'valid':
                    $query->valid();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'revoked':
                    $query->revoked();
                    break;
            }
        }

        // ユーザーでフィルタリング
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // 期限切れ間近のトークンをハイライト
        $expiringSoonTokens = ApiToken::valid()->get()->filter(function ($token) {
            return $token->isExpiringSoon(24); // 24時間以内に期限切れ
        });

        $tokens = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::orderBy('name')->get();

        return view('api-tokens.index', compact('tokens', 'users', 'expiringSoonTokens'));
    }

    /**
     * 新しいトークン作成フォームを表示
     */
    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('api-tokens.create', compact('users'));
    }

    /**
     * 新しいトークンを保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $abilities = $request->abilities ?? ['*'];
            $expiresAt = $request->expires_at ? Carbon::parse($request->expires_at) : null;

            $token = ApiToken::generate($user, $request->name, $abilities, $expiresAt);

            Log::info('API token created', [
                'token_id' => $token->id,
                'user_id' => $user->id,
                'name' => $request->name,
                'abilities' => $abilities,
                'expires_at' => $expiresAt,
            ]);

            // 生成されたトークンを一度だけ表示するためのセッション
            session()->flash('new_token', $token->id);

            return redirect()->route('api-tokens.show', $token)
                ->with('success', 'APIトークンが作成されました。一度だけ表示されるトークンを確認してください。');

        } catch (\Exception $e) {
            Log::error('Failed to create API token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'name' => $request->name,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'APIトークンの作成中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * トークンの詳細を表示
     */
    public function show(ApiToken $apiToken)
    {
        // 新しく作成されたトークンの場合はプレーンテキストを表示
        $plainToken = null;
        if (session()->has('new_token') && session('new_token') === $apiToken->id) {
            // このトークンは新しく作成されたものなので、プレーンテキストを取得
            // 注意: 実際の運用では、この方法ではなく別の方法でトークンを表示すべき
            $plainToken = 'token_' . $apiToken->id . '_example'; // デモ用
        }

        return view('api-tokens.show', compact('apiToken', 'plainToken'));
    }

    /**
     * トークンを取り消し
     */
    public function revoke(ApiToken $apiToken)
    {
        try {
            $apiToken->revoke();

            Log::info('API token revoked', [
                'token_id' => $apiToken->id,
                'user_id' => $apiToken->user_id,
            ]);

            return redirect()->route('api-tokens.index')
                ->with('success', 'APIトークンを取り消しました。');

        } catch (\Exception $e) {
            Log::error('Failed to revoke API token', [
                'token_id' => $apiToken->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'APIトークンの取り消し中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * トークンを再有効化
     */
    public function unrevoke(ApiToken $apiToken)
    {
        try {
            $apiToken->unrevoke();

            Log::info('API token unrevoked', [
                'token_id' => $apiToken->id,
                'user_id' => $apiToken->user_id,
            ]);

            return redirect()->route('api-tokens.index')
                ->with('success', 'APIトークンを再有効化しました。');

        } catch (\Exception $e) {
            Log::error('Failed to unrevoke API token', [
                'token_id' => $apiToken->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'APIトークンの再有効化中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * トークンを再生成
     */
    public function regenerate(ApiToken $apiToken)
    {
        try {
            $newToken = $apiToken->regenerate();

            Log::info('API token regenerated', [
                'token_id' => $apiToken->id,
                'user_id' => $apiToken->user_id,
            ]);

            // 新しいトークンをセッションに保存（一度だけ表示）
            session()->flash('regenerated_token', $newToken);

            return redirect()->route('api-tokens.show', $apiToken)
                ->with('success', 'APIトークンを再生成しました。新しいトークンを確認してください。');

        } catch (\Exception $e) {
            Log::error('Failed to regenerate API token', [
                'token_id' => $apiToken->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'APIトークンの再生成中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 期限切れのトークンを一括削除
     */
    public function cleanupExpired()
    {
        try {
            $expiredTokens = ApiToken::expired()->get();
            $count = $expiredTokens->count();

            foreach ($expiredTokens as $token) {
                $token->delete();
            }

            Log::info('Expired API tokens cleaned up', [
                'count' => $count,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('api-tokens.index')
                ->with('success', $count . '件の期限切れトークンを削除しました。');

        } catch (\Exception $e) {
            Log::error('Failed to cleanup expired API tokens', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', '期限切れトークンの削除中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * トークンの使用統計を表示
     */
    public function statistics()
    {
        $stats = [
            'total' => ApiToken::count(),
            'valid' => ApiToken::valid()->count(),
            'expired' => ApiToken::expired()->count(),
            'revoked' => ApiToken::revoked()->count(),
            'expiring_soon' => ApiToken::valid()->get()->filter(function ($token) {
                return $token->isExpiringSoon(24);
            })->count(),
        ];

        // 最近のアクティビティ
        $recentTokens = ApiToken::orderBy('created_at', 'desc')->limit(10)->get();

        return view('api-tokens.statistics', compact('stats', 'recentTokens'));
    }
}
