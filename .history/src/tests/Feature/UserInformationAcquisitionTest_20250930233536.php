<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserInformationAcquisitionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $generalUser1;
    protected $generalUser2;
    protected $testAttendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを作成
        $this->adminUser = User::factory()->create(['role' => 'admin']);

        // 一般ユーザーを作成
        $this->testUser = User::factory()->create(['name' => 'テスト太郎', 'role' => 'employee']);

        $this->testUser = User::factory()->create(['name' => 'テスト次郎', 'role' => 'employee']);

        // テスト用の勤怠データを作成（ユーザー1用）
        $this->testAttendance = Attendance::factory()->create([
            'user_id' => $this->generalUser1->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::today()->hour(9)->minute(0)->second(0),
            'check_out_time' => Carbon::today()->hour(18)->minute(0)->second(0),
        ]);
        // 休憩時間も追加（必要であれば）
        BreakTime::factory()->create([
            'attendance_id' => $this->testAttendance->id,
            'start_time' => Carbon::today()->hour(12)->minute(0)->second(0),
            'end_time' => Carbon::today()->hour(13)->minute(0)->second(0),
        ]);
    }
}
