<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaks;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    //テスト実行時の現在日時を固定し、
    //各テストの終了後に固定した日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_before_work_status_is_displayed_correctly()
    {
        //勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // テストの日時を固定
        Carbon::setTestNow(Carbon::create(2025, 07, 27, 9, 0, 0));

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        //画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    //出勤中ステータス
    public function test_working_status_is_displayed_correctly()
    {

        $testDateTime = Carbon::create(2025, 07, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        //前提条件となるユーザーをファクトリを使って生成
        //ステータスが出勤中のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        //前提条件となる勤怠記録をファクトリを使って生成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(1), // 1時間前出勤
            'closing_time' => null, // まだ退勤していない
        ]);

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        //画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    //休憩中ステータス
    public function test_on_break_status_is_displayed_correctly()
    {
        // テストの日時を固定
        $testDateTime = Carbon::create(2025, 07, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(3), // 3時間前出勤
            'closing_time' => null,
        ]);

        // 休憩を開始
        Breaks::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => $testDateTime->copy()->subMinutes(30), // 30分前に休憩開始
            'end_time' => null, // まだ休憩終了していない
        ]);

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        //画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('休憩中');

    }

    //退勤済ステータス
    public function test_finished_work_status_is_displayed_correctly()
    {
        $testDateTime = Carbon::create(2025, 07, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(9), // 9時間前出勤
            'closing_time' => $testDateTime->copy()->subHour(), // 1時間前に退勤済み
        ]);

        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

}
