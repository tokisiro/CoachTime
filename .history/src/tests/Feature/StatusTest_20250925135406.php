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
        Carbon::setTestNow(Carbon::create(202, 10, 27, 9, 0, 0));

        // 2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        // 3. 画面に表示されているステータスを確認する
        $response->assertStatus(200);
        $response->assertSee('勤務外'); // 期待挙動: 画面上に「勤務外」と表示される

        // 退勤ボタンや休憩ボタンなどが表示されていないことも確認できる
        $response->assertDontSee('退勤');
        $response->assertDontSee('休憩');
        $response->assertSee('出勤'); // 出勤ボタンが表示されていることを確認
    }

}
