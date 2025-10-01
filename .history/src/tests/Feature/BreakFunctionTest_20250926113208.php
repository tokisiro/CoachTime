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
    
    public function test_break_start_button_functions_correctly()
    {
        // 1. ステータスが出勤中のユーザーにログインする
        $testDateTime = Carbon::create(2025, 10, 27, 9, 0, 0); // 出勤時刻
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        // まず出勤する (attendanceレコードが必要)
        $this->post(route('record.attendance'));

        // 打刻画面にアクセス
        $response = $this->get(route('attendance'));

        $response->assertStatus(200);

        // 2. 画面に「休憩入」ボタンが表示されていることを確認する
        $response->assertSee('休憩入');
        $response->assertDontSee('休憩戻'); // まだ休憩中ではないので「休憩戻」はないはず

        // 3. 休憩の処理を行う (休憩入ボタンを押す)
        $this->post(route('break.start')); // ルーティング名を適宜調整してください

        // 期待挙動: 処理後に画面上に表示されるステータスが「休憩中」になる
        // 打刻画面を再表示して確認
        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('休憩中'); // ステータスが休憩中になっていること
        $response->assertDontSee('出勤中'); // 出勤中ではないこと
        $response->assertDontSee('休憩入'); // 休憩入ボタンは消えてるはず
        $response->assertSee('休憩戻'); // 休憩戻ボタンが表示されていること

        // データベースに休憩レコードが作成されたことも確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => Attendance::where('user_id', $user->id)
                                        ->where('date', $testDateTime->toDateString())
                                        ->first()->id,
            'break_start_time' => $testDateTime->toTimeString(),
            'break_end_time' => null, // まだ休憩終了していない
        ]);
    }
}
