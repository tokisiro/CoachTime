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
        ]);

        // ユーザー2の今日の勤怠
        $user2 = User::factory()->create(['name' => 'テストユーザー2', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in_time' => '09:30:00',
            'closing_time' => '17:30:00',
        ]);

        // 昨日の勤怠 (表示されないことを確認するため)
        $yesterday = Carbon::yesterday();
        $user3 = User::factory()->create(['name' => 'テストユーザー3', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user3->id,
            'date' => $yesterday,
            'check_in_time' => '08:00:00',
            'closing_time' => '17:00:00',
        ]);

        // 管理者としてログインし、勤怠一覧画面にアクセス
        $response = $this->actingAs($this->adminUser,'admin')->get(route('admin.attendances.list'));

        $response->assertOk();
        // HTTPステータスコード200を確認

        //その日の全ユーザーの勤怠情報が正確な値になっている
        $response->assertSee($user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee($user2->name);
        $response->assertSee('09:30');
        $response->assertSee('17:30');

        // 昨日の勤怠は表示されないことを確認
        $response->assertDontSee($user3->name);
    }

    /** @test */
    public function admin_attendance_list_displays_current_date_on_load()
    {
        $today = Carbon::today()->format('Y年m月d日');

        // 管理者としてログインし、勤怠一覧画面にアクセス
        $response = $this->actingAs($this->adminUser,'admin')->get(route('admin.attendances.list'));

        $response->assertOk();

        //勤怠一覧画面にその日の日付が表示されている
        $response->assertSee($today);
    }





    /** @test */
    public function admin_can_navigate_to_previous_day_attendance()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $formattedYesterday = $yesterday->format('Y年m月d日');

        // 昨日の勤怠データを作成
        $user1 = User::factory()->create(['name' => '前日ユーザー', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $yesterday,
            'check_in_time' => '08:30:00',
            'closing_time' => '17:30:00',
        ]);

        // 今日の勤怠データ (前日ページでは表示されないことを確認するため)
        $user2 = User::factory()->create(['name' => '今日ユーザー', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
        ]);

        // 管理者としてログインし、勤怠一覧画面（前日へのリンクを含む）にアクセス
        // 通常は /admin/attendance/list?date=YYYY-MM-DD のようなURLになる
        $response = $this->actingAs($this->adminUser,'admin')->get(route('admin.attendances.list', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertOk(); // HTTPステータスコード200を確認

        // 期待挙動: 前日の日付の勤怠情報が表示される
        $response->assertSee($formattedYesterday);
        $response->assertSee($user1->name);
        $response->assertSee('08:30');
        $response->assertSee('17:30');

        // 今日の勤怠は表示されないことを確認
        $response->assertDontSee($user2->name);
    }




    /** @test */
    public function admin_can_navigate_to_next_day_attendance()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $formattedTomorrow = $tomorrow->format('Y年m月d日');

        // 翌日の勤怠データを作成
        $user1 = User::factory()->create(['name' => '翌日ユーザー', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => $tomorrow,
            'check_in_time' => '10:00:00',
            'closing_time' => '19:00:00',
            'remarks' => '翌日の勤怠データ',
        ]);

        // 今日の勤怠データ (翌日ページでは表示されないことを確認するため)
        $user2 = User::factory()->create(['name' => '今日ユーザー', 'role' => 'employee']);
        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'remarks' => '今日の勤怠データ',
        ]);

        // 管理者としてログインし、勤怠一覧画面（翌日へのリンクを含む）にアクセス
        // 通常は /admin/attendance/list?date=YYYY-MM-DD のようなURLになる
        $response = $this->actingAs($this->adminUser,'admin')->get(route('admin.attendance.list', ['date' => $tomorrow->format('Y-m-d')]));

        $response->assertOk(); // HTTPステータスコード200を確認

        // 期待挙動: 翌日の日付の勤怠情報が表示される
        $response->assertSee($formattedTomorrow); // 翌日の日付が表示されていることを確認
        $response->assertSee($user1->name);
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        // 今日の勤怠は表示されないことを確認
        $response->assertDontSee($user2->name);
        
    }
}

