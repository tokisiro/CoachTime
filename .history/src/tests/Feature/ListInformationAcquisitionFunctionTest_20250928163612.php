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
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        // テストごとにデータベースをマイグレーションし、シードを実行
        $this->seed('UsersDatabaseSeeder');

        // シードされたユーザーを取得
        $this->user = User::where('email', 'test@example.com')->first();
        $this->userNoAttendance = User::where('email', 'noattendance@example.com')->first();
        $this->now = Carbon::now();
    }

    public function testUserAttendanceIsDisplayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('attendances.list'));

        $response->assertStatus(200); // 正常に表示されること
        $response->assertSeeText($this->now->startOfMonth()->isoFormat('MM/DD(ddd)')); // 今月の最初の勤怠データの日付が表示されているか
        $response->assertSeeText($this->now->startOfMonth()->addDays(4)->isoFormat('MM/DD(ddd)')); // 今月の5つ目の勤怠データの日付が表示されているか

        // テーブルの行数で確認 (5件の勤怠データ + ヘッダー行を除く)
        // ここでは勤怠データが5件であることを直接確認します。
        // @forelse の tbody の tr 要素が5つあることを確認します。
        // empty の場合も考慮して、存在しないことを確認することも重要です。
        $response->assertSeeInOrder([
            $this->now->startOfMonth()->isoFormat('MM/DD(ddd)'),
            $this->now->startOfMonth()->addDays(1)->isoFormat('MM/DD(ddd)'),
            $this->now->startOfMonth()->addDays(2)->isoFormat('MM/DD(ddd)'),
            $this->now->startOfMonth()->addDays(3)->isoFormat('MM/DD(ddd)'),
            $this->now->startOfMonth()->addDays(4)->isoFormat('MM/DD(ddd)'),
        ]);

        // 勤怠がない月のテストユーザーでログインした場合の確認
        $responseNoAttendance = $this->actingAs($this->userNoAttendance)->get(route('attendances.list'));
        $responseNoAttendance->assertStatus(200);
        $responseNoAttendance->assertSeeText('選択された月には勤怠記録がありません。');
        $responseNoAttendance->assertDontSeeText($this->now->startOfMonth()->isoFormat('MM/DD(ddd)')); // 勤怠がないので、日付は表示されない
    }
}
