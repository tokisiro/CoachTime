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

        $targetDate = Carbon::parse('2025-10-01');

        $this->attendanceRecord1 = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'check_in_time' =>$targetDate->copy()->setHour(9)->setMinute(0),
            'closing_time' => $targetDate->copy()->setHour(18)->setMinute(0),
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => $targetDate->copy()->setHour(12)->setMinute(0)->format('H:i:s'),
            'end_time' =>  $targetDate->copy()->setHour(13)->setMinute(0)->format('H:i:s'),
        ]);

        $this->adminUser = User::factory()->create([
            'name' => '管理者',
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

    }

    /** @test */
    public function test_admin_can_view_all_pending_applications()
    {
        // 別のユーザーを作成
        $this->anotherUser = User::factory()->create();

        $targetDate = Carbon::parse('2025-10-01');

        $this->attendanceRecord2 = Attendance::factory()->create([
            'user_id' => $this->anotherUser->id,
            'date' => Carbon::today(),
            'check_in_time' =>$targetDate->copy()->setHour(9)->setMinute(0),
            'closing_time' => $targetDate->copy()->setHour(18)->setMinute(0),
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord2->id,
            'start_time' => $targetDate->copy()->setHour(12)->setMinute(0)->format('H:i:s'),
            'end_time' =>  $targetDate->copy()->setHour(13)->setMinute(0)->format('H:i:s'),
        ]);

        // 承認待ちの申請を作成
        $pendingApplication1 = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord2->id,
            'status' => 'pending',
            'proposed_check_in_time' => '08:30',
            'proposed_closing_time' => '20:30',
            'proposed_remarks' => '電車遅延',
        ]);

        // 承認済みの申請 (表示されないことを確認するため)

        $approvedApplication = Application::factory()->create([
            'user_id' => $this->anotherUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'approved',
            'proposed_check_in_time' => '09:00',
            'proposed_closing_time' => '13:15',
            'proposed_remarks' => '体調不良',
        ]);

        // 管理者としてログインし、承認待ちの申請一覧ページにアクセス
        // 修正申請一覧のルートと、承認待ちを表示するためのクエリパラメータを調整してください。
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.request', ['status' => 'pending']));

        $response->assertStatus(200);

        // 承認待ちの申請が表示されていることを確認
        $response->assertSee('承認待ち');
        $response->assertSee($pendingApplication1->user->name);
        $response->assertSee($pendingApplication1->attendance->date->format('Y/m/d'));

        // 承認済みの申請は表示されないことを確認
        $response->assertDontSee($approvedApplication->user->name);
    }

     /** @test */
    public function test_admin_can_view_all_approved_applications()
    {
        // 別のユーザーを作成
        $this->anotherUser = User::factory()->create();

        $targetDate = Carbon::parse('2025-10-01');

        // 承認済みの申請を作成
        $approvedApplication = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'approved',
            'proposed_check_in_time' => '09:00',
            'proposed_closing_time' => '13:15',
            'proposed_remarks' => '体調不良',
        ]);


        // 管理者としてログインし、承認済みの申請一覧ページにアクセス
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.request', ['status' => 'approved']));

        $response->assertStatus(200);

        // 承認済みの申請が表示されていることを確認
        $response->assertSee($approvedApplication->user->name);
        $response->assertSee($approvedApplication->attendance->date->format('Y/m/d'));
        $response->assertSee('承認済み');

    }


    /** @test */
    public function test_admin_can_view_application_details()
    {
        $pendingApplication1 = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'pending',
            'proposed_check_in_time' => '08:30',
            'proposed_closing_time' => '20:30',
            'proposed_remarks' => '残業',
        ]);

        // 管理者としてログインし、申請詳細ページにアクセス
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showRequest',['id' => $this->attendanceRecord1->id]));

        $response->assertStatus(200);

        // 申請内容が正しく表示されていることを確認
        $response->assertSee('承認');
        $response->assertSee($pendingApplication1->user->name);
        $response->assertSee($pendingApplication1->attendance->date->format('Y年'));
        $response->assertSee($pendingApplication1->attendance->date->format('m月d日'));


    }

    /** @test */
    public function test_admin_can_approve_application_and_update_attendance()
    {

        // 承認待ちの修正申請を作成
        $pendingApplication1 = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord2->id,
            'status' => 'pending',
            'proposed_check_in_time' => '07:30',
            'proposed_closing_time' => '10:30',
            'proposed_remarks' => '幼稚園呼び出し',
        ]);

        // 管理者としてログインし、承認処理を実行
        // 通常はPATCHまたはPUTメソッドで更新エンドポイントを呼び出します
        $response = $this->actingAs($this->admin)->patch(route('admin.applications.approve', $application));

        $response->assertStatus(302); // リダイレクト（通常は一覧画面などにリダイレクト）
        $response->assertSessionHas('success', '修正申請を承認しました。'); // 成功メッセージを想定

        // データベースに修正申請のステータスが 'approved' に更新されていることを確認
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);

        // 勤怠情報が更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_in_time' => '08:30:00', // 修正後の出勤時刻
            'closing_time' => '17:30:00',  // 修正後の退勤時刻
            // working_minutes も更新されるはずなので、計算して確認
            // (17:30 - 08:30) - (13:15 - 12:15) = 9時間 - 1時間 = 8時間 = 480分
            // もし休憩時間が変更され、working_minutesのロジックがコントローラにある場合
            // 'working_minutes' => (Carbon::parse('17:30:00')->diffInMinutes(Carbon::parse('08:30:00'))) - (Carbon::parse('13:15:00')->diffInMinutes(Carbon::parse('12:15:00'))),
        ]);

        // 休憩情報が更新されていることを確認
        $this->assertDatabaseHas('breaks', [
            'id' => $break->id,
            'start_time' => '12:15:00', // 修正後の休憩開始時刻
            'end_time' => '13:15:00',   // 修正後の休憩終了時刻
        ]);
    }
}
