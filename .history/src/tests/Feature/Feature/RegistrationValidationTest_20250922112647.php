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
}
