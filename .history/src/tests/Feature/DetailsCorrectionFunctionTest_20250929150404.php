<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Breaks;
use App\Models\User;
use Carbon\Carbon;


class DetailsCorrectionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザーを作成し、ログイン
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // テスト用の勤怠データを作成（休憩データも必要であればここで作成）
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::today()->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::today()->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);

        // 必要であれば、休憩データもここで作成しておく
        Breaks::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => Carbon::today()->setHour(12)->setMinute(0)->toDateTimeString(),
            'end_time' => Carbon::today()->setHour(13)->setMinute(0)->toDateTimeString(),
        ]);
    }

    // --- ここから各テストケース ---
}

    /** @test */
    public function it_shows_error_when_check_in_time_is_after_closing_time()
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする (setUp で完了)
        // 2. 勤怠詳細ページを開く (直接POSTするので、GETリクエストは不要)

        // 3. 出勤時間を退勤時間より後に設定する
        $invalidCheckInTime = Carbon::parse($this->attendance->closing_time)->addHour()->format('H:i'); // 退勤時間より1時間後
        $validClosingTime = Carbon::parse($this->attendance->closing_time)->format('H:i');

        $response = $this->post(route('attendance.applyEdit', ['attendance_id' => $this->attendance->id]), [
            'check_in_time' => $invalidCheckInTime,
            'closing_time' => $validClosingTime,
            'proposed_remarks' => 'テスト備考', // 備考は必須なので、有効な値を設定
            // 既存の休憩時間も渡す必要がある場合
            'breaks' => [
                [
                    'id' => $this->attendance->breaks->first()->id,
                    'start_time' => Carbon::parse($this->attendance->breaks->first()->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($this->attendance->breaks->first()->end_time)->format('H:i'),
                ]
            ]
        ]);

        // 期待挙動：「出勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('check_in_time'); // check_in_time フィールドにエラーがあることを確認
        $response->assertSee('出勤時間が不適切な値です'); // エラーメッセージのテキストを確認
        $response->assertRedirect(); // バリデーションエラー時はリダイレクトされることを確認 (通常、元のフォームへ)
    }
}
