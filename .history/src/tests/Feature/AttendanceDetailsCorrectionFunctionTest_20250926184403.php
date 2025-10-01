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

        // 勤怠詳細ページを開く (修正フォームのURLを想定)
        $response = $this->actingAs($user)->get(route('attendance.showDetails', $attendance->id));
        $response->assertStatus(200);

        // 出勤時間を退勤時間より後に設定し、保存処理をする
        $response = $this->actingAs($user)->patch(route('attendance.update', $attendance->id), [
            'date' => $attendance->date,
            'check_in_time' => '19:00:00', // 不正な出勤時間
            'closing_time' => '18:00:00', // 既存の退勤時間
            // 休憩時間や備考は既存のものを渡すか、適切に設定する
            'break_start_time_1' => '12:00:00',
            'break_end_time_1' => '13:00:00',
            'break_start_time_2' => '15:00:00',
            'break_end_time_2' => '15:30:00',
            'note' => 'テスト備考', // 備考は必須の場合
        ]);

        // 期待挙動: 「出勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['check_in_time']); // check_in_timeに関するエラーがあるか
        $response->assertSee('出勤時間が不適切な値です'); // 特定のエラーメッセージを確認
        // または、リダイレクト後にエラーが表示されることを確認
        // $response->assertRedirect(route('attendance.edit', $attendance->id));
        // $response->followRedirects()->assertSee('出勤時間が不適切な値です');
    }
}
