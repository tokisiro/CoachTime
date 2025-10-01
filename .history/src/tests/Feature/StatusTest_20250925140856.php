<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaks;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    //テスト実行時の現在日時を固定し、
    //各テストの終了後に固定した日時を解除
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_before_work_status_is_displayed_correctly()
    {
        //勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // テストの日時を固定
        Carbon::setTestNow(Carbon::create(2025, 07, 27, 9, 0, 0));

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        //画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        $response->assertDontSee('退勤');
        $response->assertDontSee('休憩');
        $response->assertSee('出勤'); // 出勤ボタンが表示されていることを確認
    }

    public function test_working_status_is_displayed_correctly()
    {

        $testDateTime = Carbon::create(2023, 10, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        //ステータスが出勤中のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(1), // 1時間前出勤
            'closing_time' => null, // まだ退勤していない
        ]);

        // 2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        // 3. 画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('出勤中'); // 期待挙動: 画面上に「出勤中」と表示される

        // 出勤中なので退勤ボタンや休憩ボタンが表示されていることを確認
        $response->assertSee('退勤');
        $response->assertSee('休憩');
        $response->assertDontSee('出勤'); // 出勤ボタンが表示されていないことを確認
    }

}
