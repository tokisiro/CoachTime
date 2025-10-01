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
        $response->assertSee('お疲れ様でした。');
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

}
