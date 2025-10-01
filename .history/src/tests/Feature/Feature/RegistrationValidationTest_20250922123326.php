<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;
    //各テストメソッドの実行前にデータベースがマイグレーションされ
    // テスト終了後にロールバックされます。
    //これにより、テストが他のテストや実際のデータに影響を与えず
    // 毎回クリーンな状態で実行できる

    public function test_name_is_required_for_registration(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123', // パスワード確認フィールドも必要
            // 'name' は意図的に含めない
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_email_is_required_for_registration(): void
    {
        $userData = [
            'name' => 'Test User', // 'name' は含める
            // 'email' は意図的に含めないことで、未入力の状態をシミュレート
            'password' => 'password123',
            'password_confirmation' => 'password123', // パスワード確認は通常必須
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);

        $response->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'name' => 'Test User',
        ]);
    }

    public function test_password_is_at_least_8_characters(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short12', // 7文字で8文字未満
            'password_confirmation' => 'short12',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください。']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_password_and_confirmation_must_match(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch456', // 一致しないパスワード
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません。']);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_password_is_required_for_registration(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            // 'password' と 'password_confirmation' を意図的に含めない
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['password' => 'パスワードは必須項目です。']); // ★要確認

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
