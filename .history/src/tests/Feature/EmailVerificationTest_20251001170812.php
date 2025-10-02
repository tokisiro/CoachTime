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

    /** @test */
    public function test_a_verification_email_is_sent_after_registration()
    {
        $mailhogApi = 'http://localhost:8025/api/v2/messages';

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
            'email_verified_at' => null,
        ]);

        $this->assertAuthenticated();

        $mailhogResponse = Http::get($mailhogApi);
        $messages = $mailhogResponse -> json()['items'];

        $this -> assertNotEmpty($messages, 'No email were sent.');

        $latestEmail =$messages[0];

        $this -> assertStringContainsString('email',$latestEmail['To'][0]);

        $this -> assertStringContainsString(
            'verify-email',
            $latestEmail['Content']['Body'],
            'Verification link not found in email'
        );

    }

}
