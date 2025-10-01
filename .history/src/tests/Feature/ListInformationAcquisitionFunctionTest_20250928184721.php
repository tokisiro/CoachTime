<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;


class ListInformationAcquisitionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $userNoAttendance;
    protected $now;

    protected $userAttendanceData1;
    protected $userAttendanceData2;
    protected $otherUserAttendanceData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->userNoAttendance = User::factory()->create([
            'name' => '勤怠なしユーザー',
            'email' => 'noattendance@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->now = Carbon::now();

        $date1 = (clone $this->now)->subDays(2);
        $checkIn1 = (clone $date1)->setHour(9)->setMinute(0);
        $closing1 = (clone $date1)->setHour(17)->setMinute(0);
        $breakMinutesForCalculation1 = 60; // 休憩時間は計算用として保持 (DBには保存しない)
        $workingMinutes1 = Carbon::parse($checkIn1)->diffInMinutes(Carbon::parse($closing1)) - $breakMinutesForCalculation1;
        $workingMinutes1 = max(0, $workingMinutes1);

        $this->userAttendanceData1 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->now->subDays(2)->toDateString(),
            'check_in_time' => $checkIn1,
            'closing_time' => $closing1,
            'working_minutes' => $workingMinutes1, // ここで計算して渡す
            'status' => 'submitted',
        ]);

        \App\Models\Breaks::factory()->create([
        'attendance_id' => $this->userAttendanceData1->id,
        'start_time' => (clone $checkIn1)->addHours(4)->toTimeString(),
        'end_time' => (clone $checkIn1)->addHours(5)->toTimeString(),
        ]);

        \App\Models\Breaks::factory()->create([
        'attendance_id' => $this->userAttendanceData1->id,
    'start_time' => (clone $checkIn1)->addHours(6)->toTimeString(),
    'end_time' => (clone $checkIn1)->addHours(6)->addMinutes(30)->toTimeString(),
]);

        $date2 = (clone $this->now)->subDays(1);
        $checkIn2 = (clone $date2)->setHour(9)->setMinute(30);
        $closing2 = (clone $date2)->setHour(17)->setMinute(30);
        $breakMinutesForCalculation2 = 60;
        $workingMinutes2 = Carbon::parse($checkIn2)->diffInMinutes(Carbon::parse($closing2)) - $breakMinutesForCalculation2;
        $workingMinutes2 = max(0, $workingMinutes2);

        $this->userAttendanceData2 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->now->subDays(1)->toDateString(),
            'check_in_time' => $checkIn2,
            'closing_time' => $closing2,
            'working_minutes' => $workingMinutes2, // ここで計算して渡す
            'status' => 'submitted',
        ]);

        $dateOther = (clone $this->now)->subDays(3);
        $checkInOther = (clone $dateOther)->setHour(10)->setMinute(0);
        $closingOther = (clone $dateOther)->setHour(19)->setMinute(0);
        $breakMinutesForCalculationOther = 60;
        $workingMinutesOther = Carbon::parse($checkInOther)->diffInMinutes(Carbon::parse($closingOther)) - $breakMinutesForCalculationOther;
        $workingMinutesOther = max(0, $workingMinutesOther);

        $this->otherUserAttendanceData = Attendance::factory()->create([
            'user_id' => $this->userNoAttendance->id,
            'date' => $this->now->subDays(3)->toDateString(),
            'check_in_time' => $checkInOther,
            'closing_time' => $closingOther,
            'working_minutes' => $workingMinutesOther, // ここで計算して渡す
            'status' => 'submitted',
        ]);
    }

    public function testUserAttendanceIsDisplayed(): void
    {
        $this->actingAs($this->user);

        // 勤怠一覧ページにアクセス
        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200);

        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->date)->isoFormat('MM/DD(ddd)')); // 例: 09/28(日)
        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->check_in_time)->format('H:i')); // 例: 09:00
        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->closing_time)->format('H:i')); // 例: 17:00


        // 2件目の自分の勤怠データが表示されているかを確認
        $response->assertSeeText(Carbon::parse($this->userAttendanceData2->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSeeText(Carbon::parse($this->userAttendanceData2->check_in_time)->format('H:i'));
        $response->assertSeeText(Carbon::parse($this->userAttendanceData2->closing_time)->format('H:i'));


        // --- 他のユーザーの勤怠情報が表示されていないことを確認 ---

        // 他のユーザーの勤怠情報が表示されていないことをアサート
        // 例: 他のユーザーの出勤時間が表示されていないことを確認
        $response->assertDontSeeText(Carbon::parse($this->otherUserAttendanceData->check_in_time)->format('H:i'));
        // 必要に応じて、他のユーザーの特定の日付なども確認
        $response->assertDontSeeText(Carbon::parse($this->otherUserAttendanceData->date)->isoFormat('MM/DD(ddd)'));
    }
}
