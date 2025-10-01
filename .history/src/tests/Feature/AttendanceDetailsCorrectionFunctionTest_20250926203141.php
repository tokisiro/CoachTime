<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceDetailsCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUpAttendanceData($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }


        Carbon::setTestNow(Carbon::create(2025, 10, 27, 9, 0, 0));
        $this->actingAs($user)->post(route('record.attendance'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 18, 0, 0));
        $this->actingAs($user)->post(route('record.closing_time'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 12, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 13, 0, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 30, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        // 作成された勤怠レコードを取得して返す
        return Attendance::where('user_id', $user->id)
            ->where('date', '2025-10-27')
            ->first();
    }

    public function test_check_in_after_closing_time_shows_error()
    {
        $user = User::factory()->create();
        $attendance = $this->setUpAttendanceData($user);

        $this->actingAs($user)->get(route('attendance.showDetails', $attendance->id)->assertStatus(200);

        // 出勤時間を退勤時間より後に設定し、保存処理をする
        $response = $this->actingAs($user)->post(route('applications', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '19:00:00',
            'closing_time' => '18:00:00',
            'start_time_1' => '12:00:00',
            'end_time_1' => '13:00:00',
            'start_time_2' => '15:00:00',
            'end_time_2' => '15:30:00',
            'note' => 'テスト備考',
        ]);

        $response->assertStatus(302);

        // セッションにエラーが保存されていることを確認
        $response->assertSessionHasErrors(['check_in_time']);

        // ★★★ 変更点: $this->followRedirects() に $response を渡して、その結果を変数に代入 ★★★
        $finalResponse = $this->followRedirects($response);

        // 最終的なページにエラーメッセージが表示されていることを確認
        $finalResponse->assertSee('出勤時間が不適切な値です');
    }
}
