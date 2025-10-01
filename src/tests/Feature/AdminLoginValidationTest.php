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

    protected function createAdminUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'admin',
        ], $attributes));
    }

    public function test_admin_email_is_required_for_login(): void
    {
        $this->createAdminUser();

        $loginData = [
            'email' => '',
            'password' => 'adminpassword',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
        $response->assertRedirect();
        $this->assertGuest('admin');
    }

    public function test_admin_password_is_required_for_login(): void
    {
        $this->createAdminUser();

        $loginData = [
            'email' => 'admin@example.com',
            'password' => '', // 未入力
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
        $response->assertRedirect(); // リダイレクトされる
        $this->assertGuest('admin');
    }

    public function test_admin_invalid_credentials_show_error_message(): void
    {
        $this->createAdminUser();

        $loginData = [
            'email' => 'wrong_admin@example.com',
            'password' => 'adminpassword',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません。']);
        $response->assertRedirect();
        $this->assertGuest('admin');
    }
}
