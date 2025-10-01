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
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'pending',
            'proposed_check_in_time' => '07:30',
            'proposed_closing_time' => '10:30:00',
            'proposed_remarks' => '幼稚園呼び出しにより早退希望',
        ]);

        // 勤怠レコードに関連する既存の休憩レコードを作成
        $existingBreak = Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $proposedBreakForApplication = Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'application_id' => $pendingApplication1->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'proposed_start_time' => '09:30',
            'proposed_end_time' => '09:40',
        ]);


        // 管理者としてログインし、承認処理を実行
        $response = $this->actingAs($this->adminUser, 'admin')
        ->post(route('admin.approve', $this->attendanceRecord1->id));

        // リダイレクトされることを確認
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('attendance.showDetails', ['id' => $this->attendanceRecord1->id, 'approved' => true]));

        // データベースに修正申請のステータスが 'approved' に更新されていることを確認
        $this->assertDatabaseHas('applications', [
            'id' => $pendingApplication1->id,
            'user_id' => $this->testUser->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'status' => 'approved',
        ]);

        // 勤怠情報が更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendanceRecord1->id,
            'check_in_time' => '07:30',
            'closing_time' => '10:30',
            'remarks' => '幼稚園呼び出しにより早退希望',
        ]);

        // 既存の休憩は更新されないことを確認 (このテストケースでは)
        $this->assertDatabaseHas('breaks', [
            'id' => $existingBreak->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'application_id' => null,
        ]);

        // 申請に紐づく休憩レコードが更新され、application_id が null になっていることを確認
        $this->assertDatabaseHas('breaks', [
            'id' => $proposedBreakForApplication->id,
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => '09:30',
            'end_time' => '09:40',
            'proposed_start_time' => null,
            'proposed_end_time' => null,
            'application_id' => null,
        ]);
    }
    }

