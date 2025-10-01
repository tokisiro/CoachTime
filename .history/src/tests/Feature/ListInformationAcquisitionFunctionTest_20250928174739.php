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

        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->date)->isoFormat('MM/DD(ddd)')); // 例: 09/28(日)
        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->check_in_time)->format('H:i')); // 例: 09:00
        $response->assertSeeText(Carbon::parse($this->userAttendanceData1->closing_time)->format('H:i')); // 例: 17:00
        // 必要に応じて休憩時間や合計時間もアサート
        // $response->assertSeeText(sprintf('%02d:%02d', floor($this->userAttendanceData1->break_minutes / 60), $this->userAttendanceData1->break_minutes % 60));
        // $response->assertSeeText(sprintf('%02d:%02d', floor($this->userAttendanceData1->working_minutes / 60), $this->userAttendanceData1->working_minutes % 60));


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
