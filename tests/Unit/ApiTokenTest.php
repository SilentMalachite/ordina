<?php

namespace Tests\Unit;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * APIトークンの生成テスト
     */
    public function test_generate_creates_valid_token()
    {
        $user = User::factory()->create();
        $abilities = ['read', 'write'];

        $token = ApiToken::generate($user, 'Test Token', $abilities);

        $this->assertInstanceOf(ApiToken::class, $token);
        $this->assertEquals('Test Token', $token->name);
        $this->assertEquals($user->id, $token->user_id);
        $this->assertEquals($abilities, $token->abilities);
        $this->assertNotNull($token->token); // ハッシュ化されたトークン
        $this->assertTrue($token->isValid());
        $this->assertNull($token->expires_at); // デフォルトは無期限
    }

    /**
     * 有効期限付きトークンの生成テスト
     */
    public function test_generate_with_expiry()
    {
        $user = User::factory()->create();
        $expiresAt = Carbon::now()->addDays(30);

        $token = ApiToken::generate($user, 'Expiring Token', ['read'], $expiresAt);

        $this->assertEquals($expiresAt->toDateTimeString(), $token->expires_at->toDateTimeString());
        $this->assertTrue($token->isValid());
    }

    /**
     * トークンの検索テスト
     */
    public function test_find_by_token()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Test Token', ['read']);

        // トークン文字列を生成（実際の運用では保存されているはず）
        $plainToken = 'test_token_' . $token->id;

        // データベースに直接プレーンテキストを保存（テスト用）
        $token->update(['token' => hash('sha256', $plainToken)]);

        $foundToken = ApiToken::findByToken($plainToken);

        $this->assertNotNull($foundToken);
        $this->assertEquals($token->id, $foundToken->id);
    }

    /**
     * 無効なトークンの検索テスト
     */
    public function test_find_by_token_returns_null_for_invalid_token()
    {
        $foundToken = ApiToken::findByToken('invalid_token');

        $this->assertNull($foundToken);
    }

    /**
     * 有効なトークンの検証テスト
     */
    public function test_is_valid_returns_true_for_valid_token()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Valid Token', ['read']);

        $this->assertTrue($token->isValid());
    }

    /**
     * 取り消されたトークンの検証テスト
     */
    public function test_is_valid_returns_false_for_revoked_token()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Revoked Token', ['read']);
        $token->revoke();

        $this->assertFalse($token->isValid());
    }

    /**
     * 期限切れトークンの検証テスト
     */
    public function test_is_valid_returns_false_for_expired_token()
    {
        $user = User::factory()->create();
        $expiredAt = Carbon::now()->subDays(1);
        $token = ApiToken::generate($user, 'Expired Token', ['read'], $expiredAt);

        $this->assertFalse($token->isValid());
    }

    /**
     * 権限チェックテスト
     */
    public function test_can_method()
    {
        $user = User::factory()->create();

        // ワイルドカード権限のトークン
        $wildcardToken = ApiToken::generate($user, 'Wildcard Token', ['*']);
        $this->assertTrue($wildcardToken->can('read'));
        $this->assertTrue($wildcardToken->can('write'));
        $this->assertTrue($wildcardToken->can('admin'));

        // 限定された権限のトークン
        $limitedToken = ApiToken::generate($user, 'Limited Token', ['read', 'write']);
        $this->assertTrue($limitedToken->can('read'));
        $this->assertTrue($limitedToken->can('write'));
        $this->assertFalse($limitedToken->can('admin'));
    }

    /**
     * トークンの取り消しテスト
     */
    public function test_revoke_method()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Test Token', ['read']);

        $this->assertTrue($token->isValid());
        $this->assertFalse($token->revoked); // boolean castによりデフォルトはfalse

        $result = $token->revoke();

        $this->assertTrue($result);
        $this->assertFalse($token->fresh()->isValid());
        $this->assertTrue($token->fresh()->revoked);
    }

    /**
     * トークンの再有効化テスト
     */
    public function test_unrevoke_method()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Test Token', ['read']);
        $token->revoke();

        $this->assertFalse($token->isValid());

        $result = $token->unrevoke();

        $this->assertTrue($result);
        $this->assertTrue($token->fresh()->isValid());
        $this->assertFalse($token->fresh()->revoked);
    }

    /**
     * トークンの再生成テスト
     */
    public function test_regenerate_method()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Test Token', ['read']);
        $oldTokenHash = $token->token;

        $newPlainToken = $token->regenerate();

        $this->assertNotEquals($oldTokenHash, $token->fresh()->token);
        $this->assertIsString($newPlainToken);
        $this->assertGreaterThan(0, strlen($newPlainToken));
    }

    /**
     * トークンの使用記録テスト
     */
    public function test_record_usage_updates_last_used_at()
    {
        $user = User::factory()->create();
        $token = ApiToken::generate($user, 'Test Token', ['read']);

        $this->assertNull($token->last_used_at);

        $token->recordUsage();

        $this->assertNotNull($token->fresh()->last_used_at);
    }

    /**
     * 残り有効期間の取得テスト
     */
    public function test_get_remaining_time()
    {
        $user = User::factory()->create();

        // 無期限トークン
        $unlimitedToken = ApiToken::generate($user, 'Unlimited Token', ['read']);
        $this->assertNull($unlimitedToken->getRemainingTime());

        // 有期限トークン
        $expiresAt = Carbon::now()->addHours(2);
        $limitedToken = ApiToken::generate($user, 'Limited Token', ['read'], $expiresAt);
        $remainingTime = $limitedToken->getRemainingTime();

        $this->assertIsInt($remainingTime);
        $this->assertGreaterThan(0, $remainingTime);
        $this->assertLessThanOrEqual(7200, $remainingTime); // 2時間以内
    }

    /**
     * 期限切れ間近のチェックテスト
     */
    public function test_is_expiring_soon()
    {
        $user = User::factory()->create();

        // 期限切れ間近のトークン
        $expiresSoon = Carbon::now()->addHours(12);
        $tokenSoon = ApiToken::generate($user, 'Soon Token', ['read'], $expiresSoon);
        $this->assertTrue($tokenSoon->isExpiringSoon(24));

        // まだ期限切れではないトークン
        $expiresLater = Carbon::now()->addDays(7);
        $tokenLater = ApiToken::generate($user, 'Later Token', ['read'], $expiresLater);
        $this->assertFalse($tokenLater->isExpiringSoon(24));
    }

    /**
     * 有効なトークンのスコープテスト
     */
    public function test_valid_scope()
    {
        $user = User::factory()->create();

        // 有効なトークン
        $validToken = ApiToken::generate($user, 'Valid Token', ['read']);

        // 取り消されたトークン
        $revokedToken = ApiToken::generate($user, 'Revoked Token', ['read']);
        $revokedToken->revoke();

        // 期限切れのトークン
        $expiredToken = ApiToken::generate($user, 'Expired Token', ['read'], Carbon::now()->subDay());

        $validTokens = ApiToken::valid()->get();

        $this->assertCount(1, $validTokens);
        $this->assertEquals($validToken->id, $validTokens->first()->id);
    }

    /**
     * 取り消されたトークンのスコープテスト
     */
    public function test_revoked_scope()
    {
        $user = User::factory()->create();

        // 有効なトークン
        ApiToken::generate($user, 'Valid Token', ['read']);

        // 取り消されたトークン
        $revokedToken = ApiToken::generate($user, 'Revoked Token', ['read']);
        $revokedToken->revoke();

        $revokedTokens = ApiToken::revoked()->get();

        $this->assertCount(1, $revokedTokens);
        $this->assertEquals($revokedToken->id, $revokedTokens->first()->id);
    }

    /**
     * 期限切れトークンのスコープテスト
     */
    public function test_expired_scope()
    {
        $user = User::factory()->create();

        // 有効なトークン
        ApiToken::generate($user, 'Valid Token', ['read']);

        // 期限切れのトークン
        $expiredToken = ApiToken::generate($user, 'Expired Token', ['read'], Carbon::now()->subDay());

        $expiredTokens = ApiToken::expired()->get();

        $this->assertCount(1, $expiredTokens);
        $this->assertEquals($expiredToken->id, $expiredTokens->first()->id);
    }
}
