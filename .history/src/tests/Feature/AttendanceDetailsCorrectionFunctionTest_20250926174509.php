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

        // 出勤・退勤
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 9, 0, 0));
        $this->actingAs($user)->post(route('record.attendance'));

        Carbon::setTestNow(Carbon::create(2025, 10, 27, 18, 0, 0));
        $this->actingAs($user)->post(route('record.closing_time'));

        // 休憩1
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 12, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 13, 0, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        // 休憩2
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 0, 0));
        $this->actingAs($user)->post(route('record.break_in'));
        Carbon::setTestNow(Carbon::create(2025, 10, 27, 15, 30, 0));
        $this->actingAs($user)->post(route('record.break_back'));

        // 作成された勤怠レコードを取得して返す
        return Attendance::where('user_id', $user->id)
            ->where('date', '2025-10-27')
            ->first();
    }
}
