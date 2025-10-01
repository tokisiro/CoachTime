<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


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
        $response = $this->get(route('attendances.list')); // attendance.index は実際のルート名に合わせてください

        $response->assertStatus(200);

        // 自分の勤怠情報が全て表示されていることを確認
        // 例: このユーザーの勤怠レコード数を取得
        $userAttendancesCount = Attendance::where('user_id', $this->user->id)->count();

        // ページに表示される勤怠情報が、このユーザーの勤怠レコード数と一致するか確認
        // ここは実際の勤怠一覧のHTML構造に合わせて調整が必要です
        // 例: 'attendance-item' というクラス名を持つ要素の数を数える、または特定の文字列が含まれるか確認
        $response->assertSeeText($this->user->name); // ユーザー名が表示されているか
        // $response->assertSee('<div class="attendance-item">', false); // 勤怠アイテムのラッパーが表示されているか (HTMLとして評価)

        // より具体的なデータが表示されているかを確認するには、以下のように具体的な勤怠情報をアサートします。
        // 例えば、このユーザーの特定の出勤時間や退勤時間が表示されていることを確認
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        if ($attendance) {
            $response->assertSeeText(Carbon::parse($attendance->start_time)->format('H:i'));
            if ($attendance->end_time) {
                $response->assertSeeText(Carbon::parse($attendance->end_time)->format('H:i'));
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
