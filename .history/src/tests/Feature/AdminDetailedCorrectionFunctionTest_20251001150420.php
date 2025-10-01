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

    /**
     * テスト内容: 承認済みの修正申請が全て表示されている
     * テスト手順:
     *   1. 管理者ユーザーにログインをする
     *   2. 修正申請一覧ページを開き、承認済みのタブを開く
     * 期待挙動: 全ユーザーの承認済みの修正申請が表示される
     */
    public function test_admin_can_view_all_approved_applications()
    {
        // 別のユーザーを作成
        $this->anotherUser = User::factory()->create();

        // 承認済みの申請を作成
        $approvedApplication = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'approved',
            'proposed_check_in_time' => '09:00',
            'proposed_closing_time' => '13:15',
            'proposed_remarks' => '体調不良',
        ]);

        $approvedApplication = Application::factory()->create([
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'approved',
            'proposed_check_in_time' => '09:00',
            'proposed_closing_time' => '13:15',
            'proposed_remarks' => '体調不良',
        ]);

        // 承認待ちの申請 (表示されないことを確認するため)
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

        $approvedApplication = Application::factory()->create([
            'user_id' => $this->anotherUser->id,
            'attendance_id' => $this->attendanceRecord2->id,
            'status' => 'pending',
            'proposed_check_in_time' => '10:00',
            'proposed_closing_time' => '20:15',
            'proposed_remarks' => '電車遅延',
        ]);

        // 管理者としてログインし、承認済みの申請一覧ページにアクセス
        $response = $this->actingAs($this->admin)->get(route('admin.request', ['status' => 'approved']));

        $response->assertStatus(200);

        // 承認済みの申請が表示されていることを確認
        $response->assertSee($approvedApplication1->user->name);
        $response->assertSee($approvedApplication1->attendance->date->format('Y/m/d'));
        $response->assertSee('承認済み');
        $response->assertSee($approvedApplication2->user->name);
        $response->assertSee($approvedApplication2->attendance->date->format('Y/m/d'));
        $response->assertSee('承認済み');

        // 承認待ちの申請は表示されないことを確認
        $response->assertDontSee($pendingApplication->user->name);
    }

    /**
     * テスト内容: 修正申請の詳細内容が正しく表示されている
     * テスト手順:
     *   1. 管理者ユーザーにログインをする
     *   2. 修正申請の詳細画面を開く
     * 期待挙動: 申請内容が正しく表示されている
     */
    public function test_admin_can_view_application_details()
    {
        // 承認待ちの申請を作成
        $application = Application::factory()->create([
            'user_id' => $this->normalUser->id,
            'status' => 'pending',
            'application_date' => Carbon::parse('2023-01-15'),
            'current_check_in_time' => '09:00:00',
            'proposed_check_in_time' => '08:45:00',
            'current_closing_time' => '18:00:00',
            'proposed_closing_time' => '17:30:00',
            'current_breaks_start_time' => '12:00:00', // 休憩時間の修正も考慮
            'proposed_breaks_start_time' => '12:15:00',
            'current_breaks_end_time' => '13:00:00',
            'proposed_breaks_end_time' => '13:15:00',
            'reason' => 'カフェ開店準備のため、出社時刻を早めました。',
            'admin_notes' => '特になし', // 管理者メモがあれば
        ]);

        // 管理者としてログインし、申請詳細ページにアクセス
        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', $application));

        $response->assertStatus(200);

        // 申請内容が正しく表示されていることを確認
        $response->assertSee($application->user->name);
        $response->assertSee($application->application_date->format('Y年m月d日'));
        $response->assertSee($application->status);
        $response->assertSee('現在の出勤時刻: ' . $application->current_check_in_time); // フォーマットはビューに合わせて調整
        $response->assertSee('修正後の出勤時刻: ' . $application->proposed_check_in_time);
        $response->assertSee('理由: ' . $application->reason);

        // 休憩時間の情報も確認 (もしビューに表示するなら)
        if ($application->current_breaks_start_time) {
            $response->assertSee('現在の休憩開始: ' . $application->current_breaks_start_time);
            $response->assertSee('修正後の休憩開始: ' . $application->proposed_breaks_start_time);
        }
    }

    /**
     * テスト内容: 修正申請の承認処理が正しく行われる
     * テスト手順:
     *   1. 管理者ユーザーにログインをする
     *   2. 修正申請の詳細画面で「承認」ボタンを押す
     * 期待挙動: 修正申請が承認され、勤怠情報が更新される
     */
    public function test_admin_can_approve_application_and_update_attendance()
    {
        // 勤怠データを作成（修正申請の対象となる勤怠）
        $attendance = Attendance::factory()->create([
            'user_id' => $this->normalUser->id,
            'date' => Carbon::today()->subDays(2),
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'working_minutes' => 540, // 9時間
        ]);
        // 休憩データも作成
        $break = Breaks::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 承認待ちの修正申請を作成
        $application = Application::factory()->create([
            'user_id' => $this->normalUser->id,
            'attendance_id' => $attendance->id, // 関連付け
            'status' => 'pending',
            'application_date' => $attendance->date,
            'current_check_in_time' => $attendance->check_in_time,
            'proposed_check_in_time' => '08:30:00', // 出勤時刻を30分早める
            'current_closing_time' => $attendance->closing_time,
            'proposed_closing_time' => '17:30:00', // 退勤時刻を30分早める
            'current_breaks_start_time' => $break->start_time,
            'proposed_breaks_start_time' => '12:15:00', // 休憩開始を15分遅らせる
            'current_breaks_end_time' => $break->end_time,
            'proposed_breaks_end_time' => '13:15:00',   // 休憩終了を15分遅らせる
            'reason' => '修正理由',
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
