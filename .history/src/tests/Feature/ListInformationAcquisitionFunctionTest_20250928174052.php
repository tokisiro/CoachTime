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

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->userNoAttendance = User::factory()->create([
            'email' => 'noattendance@example.com',
            'password' => Hash::make('password'),
        ]);

        // 必要に応じて、これらのユーザーに紐づく勤怠データもここで作成します。
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in_time' => Carbon::now()->setHour(9)->setMinute(0),
            'closing_time' => Carbon::now()->setHour(17)->setMinute(0),
        ]);
        // 他のユーザーの勤怠データも必要に応じて作成

        $this->now = Carbon::now();
    }

    public function testUserAttendanceIsDisplayed(): void
    {
        $this->actingAs($this->user);

        // 勤怠一覧ページにアクセス
        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200);

        // 自分の勤怠情報が全て表示されていることを確認
        $userAttendancesCount = Attendance::where('user_id', $this->user->id)->count();

        $response->assertSeeText($this->user->name);
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        if ($attendance) {
            $response->assertSeeText(Carbon::parse($attendance->check_in_time)->format('H:i'));
            if ($attendance->end_time) {
                $response->assertSeeText(Carbon::parse($attendance->closing_time)->format('H:i'));
            }
        }

        // 他のユーザーの勤怠情報が表示されていないことを確認
        // 例: 他のユーザーの勤怠情報が含まれていないことを確認
        $anotherUserAttendance = Attendance::where('user_id', $this->anotherUser->id)->first();
        if ($anotherUserAttendance) {
            $response->assertDontSeeText(Carbon::parse($anotherUserAttendance->start_time)->format('H:i'));
        }
    }
}
