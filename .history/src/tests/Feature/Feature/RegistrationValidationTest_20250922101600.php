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

        
    }
}
