<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\Breaks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AdminDetailedCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $testUser;
    protected $testAttendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 通常ユーザーを作成
        $this->testUser = User::factory()->create([
            'name' => 'テスト太郎',
            'role' => 'employee',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->adminUser = User::factory()->create([
            'name' => '管理者',
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

    }

    
}
