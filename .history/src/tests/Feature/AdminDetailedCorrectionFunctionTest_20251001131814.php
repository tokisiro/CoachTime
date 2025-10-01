<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance; // 勤怠情報更新の確認用
use App\Models\Application; // 修正申請モデル
use App\Models\Breaks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminDetailedCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $testUser;
    protected $testAttendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        // テスト用の一般ユーザーを作成
        $this->testUser = User::factory()->create(['name' => 'テスト太郎', 'role' => 'employee']);

        // テスト用の勤怠データを作成
        $this->testAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::parse('2023-01-15'),
            'check_in_time' =>'09:00',
            'closing_time' => '18:00',
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->testAttendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

    }
}
