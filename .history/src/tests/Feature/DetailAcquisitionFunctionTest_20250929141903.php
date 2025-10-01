<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Breaks;
use Carbon\Carbon;

class DetailAcquisitionFunctionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
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
        $breakMinutesForCalculation1 = 60;
        $workingMinutes1 = Carbon::parse($checkIn1)->diffInMinutes(Carbon::parse($closing1)) - $breakMinutesForCalculation1;
        $workingMinutes1 = max(0, $workingMinutes1);

        $this->userAttendanceData1 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->now->subDays(2)->toDateString(),
            'check_in_time' => $checkIn1,
            'closing_time' => $closing1,
            'working_minutes' => $workingMinutes1,
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

    /** @test */
    public function DetailAcquisitionFunction()
    {
        $this->actingAs($this->user);

        // 詳細リンクがあることを想定して、特定の日の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->subDays(5)->toDateString(),
            'check_in_time' => Carbon::now()->subDays(5)->setHour(9)->setMinute(0)->toDateTimeString(),
            'closing_time' => Carbon::now()->subDays(5)->setHour(17)->setMinute(0)->toDateTimeString(),
        ]);

        // 休憩データを作成し、勤怠に関連付ける
        $break1 = \App\Models\Breaks::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::parse($attendance->check_in_time)->addHours(1)->toDateTimeString(), // 10:00
            'end_time' => Carbon::parse($attendance->check_in_time)->addHours(2)->toDateTimeString(),   // 11:00 (1時間休憩)
        ]);

        $break2 = \App\Models\Breaks::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::parse($attendance->closing_time)->subHours(2)->toDateTimeString(),
            'end_time' => Carbon::parse($attendance->closing_time)->subHours(1)->toDateTimeString(),
        ]);

        $attendance = $attendance->fresh('breaks');

        $response = $this->get(route('attendance.showDetails', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 勤怠詳細画面に遷移したことを確認
        // テスト内容1: 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
        $response->assertSeeText($this->user->name);

        // テスト内容2: 勤怠詳細画面の「日付」が選択した日付になっている
        // 年と月日を別々にアサートする
        $response->assertSeeText(Carbon::parse($attendance->date)->isoFormat('YYYY年')); // 例: 2025年
        $response->assertSeeText(Carbon::parse($attendance->date)->isoFormat('M月DD日'));  // 例: 9月23日

        // テスト内容3: 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
        $checkInTimeFormatted = Carbon::parse($attendance->check_in_time)->format('H:i');
        $closingTimeFormatted = Carbon::parse($attendance->closing_time)->format('H:i');
        $response->assertSee('value="' . $checkInTimeFormatted . '"', false);
        $response->assertSee('value="' . $closingTimeFormatted . '"', false);


        // $attendance->breaks コレクションから、$break1 と $break2 に対応するものを探す（IDなどで）
        $actualBreak1 = $attendance->breaks->firstWhere('id', $break1->id);
        $actualBreak2 = $attendance->breaks->firstWhere('id', $break2->id);


        // テスト内容4: 「休憩」にて記されている時間がログインユーザーの打刻と一致している
        $break1StartTimeFormatted = Carbon::parse($actualBreak1->start_time)->format('H:i');
        $break1EndTimeFormatted = Carbon::parse($actualBreak1->end_time)->format('H:i');
        $response->assertSee('value="' . $break1StartTimeFormatted . '"', false);

        $response->assertSee('value="' . trim($break1EndTimeFormatted) . ' "', false) || $response->assertSee('value="' . trim($break1EndTimeFormatted) . '"', false);

        $break2StartTimeFormatted = Carbon::parse($actualBreak2->start_time)->format('H:i');
        $break2EndTimeFormatted = Carbon::parse($actualBreak2->end_time)->format('H:i');
        $response->assertSee('value="' . $break2StartTimeFormatted . '"', false);

        $response->assertSee('value="' . trim($break2EndTimeFormatted) . ' "', false) || $response->assertSee('value="' . trim($break2EndTimeFormatted) . '"', false);
    }
}
