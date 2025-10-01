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

    protected function setUp(): void
    {
        parent::setUp();
        // テストごとにデータベースをマイグレーションし、シードを実行
        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        $this->seed(\Database\Seeders\AttendanceTableSeeder::class);

        // シードされたユーザーを取得
        $this->user = User::where('email', 'test@example.com')->first();
        $this->userNoAttendance = User::where('email', 'noattendance@example.com')->first();
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
