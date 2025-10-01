<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        // 非管理者ユーザーも作成 (勤怠データ用)
        $this->user = User::factory()->create(['role' => 'employee']);
    }

    /** @test */
    public function admin_can_see_all_users_attendance_for_the_current_day()
    {
        // 今日の日付を取得
        $today = Carbon::today();

        // テスト用の勤怠データを作成
        // ユーザー1の今日の勤怠
        $user1 = User::factory()->create(['name' => 'テストユーザー1', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $today,
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'proposed_remarks' => 'テストユーザー1の今日の勤怠',
        ]);

        // ユーザー2の今日の勤怠
        $user2 = User::factory()->create(['name' => 'テストユーザー2', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in_time' => '09:30:00',
            'closing_time' => '17:30:00',
            'proposed_remarks' => 'テストユーザー2の今日の勤怠',
        ]);

        // 昨日の勤怠 (表示されないことを確認するため)
        $yesterday = Carbon::yesterday();
        $user3 = User::factory()->create(['name' => 'テストユーザー3', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user3->id,
            'date' => $yesterday,
            'check_in_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'proposed_remarks' => 'テストユーザー3の昨日の勤怠',
        ]);

        // 管理者としてログインし、勤怠一覧画面にアクセス
        $response = $this->actingAs($this->adminUser)->get(route('admin.attendances.list')); // 仮定: 管理者勤怠一覧ルート

        $response->assertOk();
        // HTTPステータスコード200を確認

        //その日の全ユーザーの勤怠情報が正確な値になっている
        $response->assertSee($user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テストユーザー1の今日の勤怠');

        $response->assertSee($user2->name);
        $response->assertSee('09:30');
        $response->assertSee('17:30');
        $response->assertSee('テストユーザー2の今日の勤怠');

        // 昨日の勤怠は表示されないことを確認
        $response->assertDontSee($user3->name);
        $response->assertDontSee('テストユーザー3の昨日の勤怠');
    }
}
