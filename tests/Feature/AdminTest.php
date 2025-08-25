<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\ClosingDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('管理者');

        $this->user = User::factory()->create();
        $this->user->assignRole('一般スタッフ');
    }

    // === 管理者認証テスト ===
    
    public function test_admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertViewHas('stats');
        $response->assertViewHas('recentActivities');
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/admin');
        
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/login');
    }

    // === ユーザー管理テスト ===

    public function test_admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.users');
        $response->assertViewHas('users');
    }

    public function test_admin_can_create_new_user()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => '一般スタッフ'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $newUser = User::where('email', $userData['email'])->first();
        $this->assertTrue($newUser->hasRole('一般スタッフ'));
    }

    public function test_admin_can_create_admin_user()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => '管理者'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $newUser = User::where('email', $userData['email'])->first();
        $this->assertTrue($newUser->hasRole('管理者'));
    }

    public function test_admin_can_edit_user()
    {
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$targetUser->id}/edit");
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.edit-user');
        $response->assertViewHas('user', $targetUser);
    }

    public function test_admin_can_update_user()
    {
        $targetUser = User::factory()->create();
        $targetUser->assignRole('一般スタッフ');
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'マネージャー'
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$targetUser->id}", $updateData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('マネージャー'));
        $this->assertFalse($targetUser->hasRole('一般スタッフ'));
    }

    public function test_admin_can_delete_user()
    {
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$targetUser->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id
        ]);
    }

    public function test_admin_cannot_delete_self()
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->admin->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id
        ]);
    }

    public function test_admin_cannot_delete_user_with_related_data()
    {
        $targetUser = User::factory()->create();
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();
        
        // ユーザーに関連する取引データを作成
        Transaction::factory()->create([
            'user_id' => $targetUser->id,
            'product_id' => $product->id,
            'customer_id' => $customer->id
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$targetUser->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id
        ]);
    }

    // === データ管理テスト ===

    public function test_admin_can_view_data_management()
    {
        $response = $this->actingAs($this->admin)->get('/admin/data-management');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.data-management');
        $response->assertViewHas('stats');
    }

    public function test_admin_can_backup_data()
    {
        $response = $this->actingAs($this->admin)->post('/admin/backup');
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_clear_transaction_data()
    {
        // テストデータを作成
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $customer = Customer::factory()->create();
        Transaction::factory()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id
        ]);

        $clearData = [
            'data_type' => 'transactions',
            'confirmation' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/clear-data', $clearData);

        $response->assertRedirect('/admin/data-management');
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('transactions', 0);
        // 商品の在庫がリセットされることを確認
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 0
        ]);
    }

    // === 締め日設定テスト ===

    public function test_admin_can_view_closing_dates()
    {
        $response = $this->actingAs($this->admin)->get('/admin/closing-dates');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.closing-dates');
        $response->assertViewHas('closingDates');
    }

    public function test_admin_can_create_closing_date()
    {
        $closingData = [
            'day_of_month' => 31,
            'description' => 'Monthly closing'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/closing-dates', $closingData);

        $response->assertRedirect('/admin/closing-dates');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('closing_dates', [
            'day_of_month' => 31,
            'description' => 'Monthly closing',
            'is_active' => true,
            'updated_by' => $this->admin->id
        ]);
    }

    public function test_admin_can_delete_closing_date()
    {
        $closingDate = ClosingDate::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete("/admin/closing-dates/{$closingDate->id}");

        $response->assertRedirect('/admin/closing-dates');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('closing_dates', [
            'id' => $closingDate->id
        ]);
    }

    // === システム設定テスト ===

    public function test_admin_can_view_system_settings()
    {
        $response = $this->actingAs($this->admin)->get('/admin/system-settings');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.system-settings');
        $response->assertViewHas('settings');
    }

    // === システムログテスト ===

    public function test_admin_can_view_system_logs()
    {
        $response = $this->actingAs($this->admin)->get('/admin/system-logs');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.system-logs');
        $response->assertViewHas('logs');
    }

    // === バリデーションテスト ===

    public function test_user_creation_validation()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123', // Too short
                'password_confirmation' => '456' // Doesn't match
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_closing_date_validation()
    {
        // 既存の締め日を作成
        ClosingDate::factory()->create(['day_of_month' => 31]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/closing-dates', [
                'day_of_month' => 31, // Duplicate
                'description' => ''
            ]);

        $response->assertSessionHasErrors(['day_of_month']);
    }

    // === 権限テスト ===

    public function test_non_admin_cannot_access_user_management()
    {
        $response = $this->actingAs($this->user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_data_management()
    {
        $response = $this->actingAs($this->user)->get('/admin/data-management');
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_create_users()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->actingAs($this->user)
            ->post('/admin/users', $userData);

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_delete_data()
    {
        $clearData = [
            'data_type' => 'transactions',
            'confirmation' => true
        ];

        $response = $this->actingAs($this->user)
            ->post('/admin/clear-data', $clearData);

        $response->assertStatus(403);
    }
}