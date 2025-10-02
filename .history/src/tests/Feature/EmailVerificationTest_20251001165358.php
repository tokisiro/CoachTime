<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_a_verification_email_is_sent_after_registration()
    {
        $mailhogApi = '';

        $password = 'password123';
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        // 1. 会員登録をする
        // 登録ルートは '/register' と仮定
        $response = $this->post('/register', $userData);

        // ユーザーがデータベースに登録されていることを確認
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'email_verified_at' => null, // まだ認証されていないことを確認
        ]);

        // ユーザーがログイン状態になっていることを確認 (登録後のデフォルト動作)
        $this->assertAuthenticated();

        // 2. 認証メールを送信する (会員登録が成功すれば自動的に送信されることを検証)
        // 期待挙動: 登録したメールアドレス宛に認証メールが送信されている
        Mail::assertSent(VerifyEmail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });

        // 登録後のリダイレクト先を検証（例：/dashboard や /email/verify 誘導画面など）
        // $response->assertRedirect('/email/verify'); // メール認証誘導画面にリダイレクトする場合
        // $response->assertRedirect('/dashboard'); // 認証誘導画面を経由しない場合
    }

}
