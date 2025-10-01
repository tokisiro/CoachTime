<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class ClosingOutFunction extends TestCase
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

        $response->assertSee('退勤'); // 「退勤」ボタンが表示されていること
        $response->assertDontSee('出勤'); // まだ出勤ボタンはないはず (勤務中なので)

        // 3. 退勤の処理を行う
        $closingTime = Carbon::create(2025, 10, 27, 18, 0, 0);
        Carbon::setTestNow($closingTime);
        $this->post(route('record.closing_time')); // ルーティング名を適宜調整してください

        // 期待挙動: 処理後に画面上に表示されるステータスが「退勤済」になる
        // 打刻画面を再表示して確認
        $response = $this->get(route('attendance'));

        $response->assertStatus(200);
        $response->assertSee('退勤済'); // ステータスが退勤済になっていること
        $response->assertSee('出勤'); // 退勤したので、次は「出勤」ボタンが表示されているはず
        $response->assertDontSee('退勤'); // 退勤ボタンは消えているはず
        $response->assertDontSee('休憩入'); // 退勤済なので休憩ボタンも消えているはず
        $response->assertDontSee('休憩戻'); // 退勤済なので休憩ボタンも消えているはず

        // データベースに退勤時刻が記録されたことを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $checkInTime->toDateString(),
            'check_in_time' => $checkInTime->toTimeString(),
            'closing_time' => $closingTime->toTimeString(), // 退勤時刻が記録されている
        ]);
    }

}
