<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class BreakFunctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_break_start_button_functions_correctly()
    {
        //ステータスが出勤中のユーザーにログインする
        $testDateTime = Carbon::create(2025, 10, 27, 6, 0, 0);
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('record.attendance'));

        // 打刻画面にアクセス
        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);

        $response->assertSee('休憩入');
        $response->assertDontSee('休憩戻');

        //休憩入ボタンを押す
        $this->post(route('record.break_in'));

        // 打刻画面を再表示して確認
        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertDontSee('出勤中');
        $response->assertDontSee('休憩入');
        $response->assertSee('休憩戻');

        // データベースに休憩レコードが作成されたことも確認
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => Attendance::where('user_id', $user->id)
            ->where('date', $testDateTime->toDateString())
            ->first()->id,

            'start_time' => $testDateTime->toTimeString(),
            'end_time' => null, // まだ休憩終了していない
        ]);
    }

    public function test_multiple_breaks_can_be_taken_in_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $checkInTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($checkInTime);
        $this->post(route('record.attendance'));

        //休憩入と休憩戻の処理を行う (1回目の休憩)
        $breakStartTime1 = Carbon::create(2025, 10, 27, 12, 0, 0);
        Carbon::setTestNow($breakStartTime1);
        $this->post(route('record.break_in')); // 1回目休憩入

        $breakEndTime1 = Carbon::create(2025, 10, 27, 13, 0, 0);
        Carbon::setTestNow($breakEndTime1);
        $this->post(route('record.break_back')); // 1回目休憩戻

        //「休憩入」ボタンが表示されることを確認する
        // 打刻画面を再表示
        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
        $response->assertDontSee('休憩戻');

        // データベースに休憩レコードが2つ（開始と終了）記録されていることを確認
        $attendanceId = Attendance::where('user_id', $user->id)
            ->where('date', $checkInTime->toDateString())
            ->first()->id;

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendanceId,
            'start_time' => $breakStartTime1->toTimeString(),
            'end_time' => $breakEndTime1->toTimeString(),
        ]);

        // 次の休憩ができることを確認する
        $breakStartTime2 = Carbon::create(2025, 10, 27, 15, 0, 0);
        Carbon::setTestNow($breakStartTime2);
        $this->post(route('record.break_in'));

        $response = $this->get(route('attendance.register'));
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertDontSee('休憩入');

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendanceId,
            'start_time' => $breakStartTime2->toTimeString(),
            'end_time' => null,
        ]);
    }

    public function test_break_end_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $checkInTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($checkInTime);
        $this->post(route('record.attendance'));

        $breakStartTime = Carbon::create(2025, 10, 27, 12, 0, 0);
        Carbon::setTestNow($breakStartTime);
        $this->post(route('record.break_in'));

        $response = $this->get(route('attendance.register'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertDontSee('休憩入');

        //休憩戻の処理を行う
        $breakEndTime = Carbon::create(2025, 10, 27, 13, 0, 0);
        Carbon::setTestNow($breakEndTime);
        $this->post(route('record.break_back'));

        // 打刻画面を再表示して確認
        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertDontSee('休憩中');
        $response->assertDontSee('休憩戻');
        $response->assertSee('休憩入');

        $attendanceId = Attendance::where('user_id', $user->id)
            ->where('date', $checkInTime->toDateString())
            ->first()->id;

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendanceId,
            'start_time' => $breakStartTime->toTimeString(),
            'end_time' => $breakEndTime->toTimeString(),
        ]);
    }

    public function test_multiple_break_ends_can_be_taken_in_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $checkInTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($checkInTime);
        $this->post(route('record.attendance'));

        // 2. 休憩入と休憩戻の処理を行い
        $breakStartTime1 = Carbon::create(2025, 10, 27, 12, 0, 0);
        Carbon::setTestNow($breakStartTime1);
        $this->post(route('record.break_in')); // 1回目休憩入

        $breakEndTime1 = Carbon::create(2025, 10, 27, 13, 0, 0);
        Carbon::setTestNow($breakEndTime1);
        $this->post(route('record.break_back')); // 1回目休憩戻

        // 再度休憩入の処理を行う
        $breakStartTime2 = Carbon::create(2025, 10, 27, 15, 0, 0);
        Carbon::setTestNow($breakStartTime2);
        $this->post(route('record.break_in')); // 2回目休憩入

        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertDontSee('休憩入');

        $attendanceId = Attendance::where('user_id', $user->id)
            ->where('date', $checkInTime->toDateString())
            ->first()->id;

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendanceId,
            'break_start_time' => $breakStartTime2->toTimeString(),
            'break_end_time' => null,
        ]);
    }
}
