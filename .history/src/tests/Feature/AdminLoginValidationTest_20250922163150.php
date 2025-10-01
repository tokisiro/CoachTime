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
}
