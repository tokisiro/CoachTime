<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginValidationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected function createAdmin(array $attributes = []): Admin
    {
        return User::factory()->create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'), // 管理者用のパスワード
        ], $attributes));
    }

    public function test_admin_email_is_required_for_login(): void
    {
        $this->createAdmin();

        $loginData = [
            'email' => '',
            'password' => 'adminpassword',
        ];

        $response = $this->post('/admin/login', $loginData);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
        $response->assertRedirect();
        $this->assertGuest('admin');
    }

    public function test_admin_password_is_required_for_login(): void
    {
        $this->createAdmin();

        $loginData = [
            'email' => 'admin@example.com',
            'password' => '', // 未入力
        ];

        $response = $this->post('/admin/login', $loginData);

        // 期待挙動：指定されたバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
        $response->assertRedirect(); // リダイレクトされる
        $this->assertGuest('admin'); // 'admin' ガードでログインしていないことを確認
    }
}
