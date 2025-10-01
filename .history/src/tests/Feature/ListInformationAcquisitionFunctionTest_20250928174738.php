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

        $this->userAttendanceData1 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->now->subDays(2)->toDateString(),
            'check_in_time' => $this->now->subDays(2)->setHour(9)->setMinute(0),
            'closing_time' => $this->now->subDays(2)->setHour(17)->setMinute(0),
            'status' => 'submitted',
        ]);
        $this->userAttendanceData2 = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->now->subDays(1)->toDateString(),
            'check_in_time' => $this->now->subDays(1)->setHour(9)->setMinute(30),
            'closing_time' => $this->now->subDays(1)->setHour(17)->setMinute(30),
            'status' => 'submitted',
        ]);

        // $this->userNoAttendance の勤怠データも作成しておく (別ユーザーのデータとして利用)
        $this->otherUserAttendanceData = Attendance::factory()->create([
            'user_id' => $this->userNoAttendance->id, // 他のユーザーに紐づける
            'date' => $this->now->subDays(3)->toDateString(),
            'check_in_time' => $this->now->subDays(3)->setHour(10)->setMinute(0),
            'closing_time' => $this->now->subDays(3)->setHour(19)->setMinute(0),
            'status' => 'submitted',
        ]);
    }

    public function testUserAttendanceIsDisplayed(): void
    {
        $this->actingAs($this->user);

        // 勤怠一覧ページにアクセス
        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200);

        
    }
}
