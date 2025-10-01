<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class ClosingOutFunctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_closing_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $checkInTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($checkInTime);
        $this->post(route('record.attendance'));

        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);

        $response->assertSee('退勤');
        $response->assertSee('出勤中');

        $closingTime = Carbon::create(2025, 10, 27, 18, 0, 0);
        Carbon::setTestNow($closingTime);
        $this->post(route('record.closing_time'));

        // 打刻画面を再表示して確認
        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした');
        $response->assertDontSee('休憩入');
        $response->assertDontSee('休憩戻');

        // データベースに退勤時刻が記録されたことを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $checkInTime->toDateString(),
            'check_in_time' => $checkInTime->toTimeString(),
            'closing_time' => $closingTime->toTimeString(),
        ]);
    }

    public function test_closing_time_is_visible_on_attendance_list()
    {
        // 1. ステータスが勤務外のユーザーにログインする
        // (テストの度にRefreshDatabaseが走るので、デフォルトで勤務外の状態から始まる)
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 出勤と退勤の処理を行う
        $checkInTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($checkInTime);
        $this->post(route('record.attendance')); // 出勤

        $closingTime = Carbon::create(2025, 10, 27, 18, 0, 0);
        Carbon::setTestNow($closingTime);
        $this->post(route('record.closing_time')); // 退勤 (ルーティング名を適宜調整してください)

        // 3. 勤怠一覧画面から退勤の日付を確認する
        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200);

        // 期待挙動: 勤怠一覧画面に退勤時刻が正確に記録されている

        // 日付の確認
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $japaneseDayOfWeek = $weekdays[$checkInTime->dayOfWeek];
        $expectedDateString = $checkInTime->format('m/d(') . $japaneseDayOfWeek . ')';
        $response->assertSee($expectedDateString);

        // 出勤時刻の確認
        $response->assertSee($checkInTime->format('H:i')); // '09:00'

        // 退勤時刻の確認
        $response->assertSee($closingTime->format('H:i')); // '18:00'

        // データベースに退勤時刻が正確に記録されていることを確認 (assertDatabaseHasは前のテストで十分だが、念のため)
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $checkInTime->toDateString(),
            'check_in_time' => $checkInTime->toTimeString(),
            'closing_time' => $closingTime->toTimeString(),
        ]);
        

}
