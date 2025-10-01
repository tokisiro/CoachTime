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
    //テストユーザーを事前に作成する
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ], $attributes));
    }

    // ユーザーを登録する
    public function test_email_is_required_for_login(): void
    {
        $this->createUser();

        //メールアドレス以外のユーザー情報を入力する
        $loginData = [
            'email' => '', // 未入力の状態
            'password' => 'password123',
        ];

        //ログインの処理を行う
        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
        $response->assertRedirect();

        // ログインしていないことを確認
        $this->assertGuest();
    }

    public function test_password_is_required_for_login(): void
    {
        // 1. ユーザーを登録する
        $this->createUser();

        // 2. パスワード以外のユーザー情報を入力する (passwordを空にする)
        $loginData = [
            'email' => 'test@example.com',
            'password' => '', // 未入力の状態
        ];

        $response = $this->post('/login', $loginData);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
        $response->assertRedirect();

        $this->assertGuest();
    }

    public function test_invalid_credentials_show_error_message(): void
    {
        
        $this->createUser();

        // 2. 誤ったメールアドレスのユーザー情報を入力する (存在しないメールアドレス)
        $loginData = [
            'email' => 'wrong@example.com', // 存在しないメールアドレス
            'password' => 'password123',
        ];

        $response = $this->post('/login', $loginData);

    }
}
