<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ], $attributes));
    }

    public function test_email_is_required_for_login(): void
    {
        // 1. ユーザーを登録する（ログインフォームのテストなので、事前にユーザーが必要）
        $this->createUser();

        // 2. メールアドレス以外のユーザー情報を入力する (emailを空にする)
        $loginData = [
            'email' => '', // 未入力の状態
            'password' => 'password123',
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/login', $loginData);

        // 期待挙動：「メールアドレスは必須項目です。」というバリデーションメッセージが表示される
        // メッセージはあなたの LoginRequest.php の messages() メソッドに合わせる
        $response->assertSessionHasErrors(['email' => 'メールアドレスは必須項目です。']); // ★要確認
        $response->assertRedirect(); // バリデーションエラー時はリダイレクト

        // ログインしていないことを確認
        $this->assertGuest();
    }
}
