<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */


    public function test_check_in_button_functions_correctly()
    {
        $testDateTime = Carbon::create(2025, 07, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        //ステータスが勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        //画面に「出勤」ボタンが表示されていることを確認する
        $response->assertStatus(200);
        $response->assertSee('出勤'); // 「出勤」ボタン
        $response->assertDontSee('出勤中');

        //出勤の処理を行う
        $response = $this->post(route('record.attendance'), [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('attendance.register'));

        // リダイレクト後の画面を取得
        $response = $this->get(route('attendance.register'));

        // 期待挙動：画面上に表示されるステータスが「出勤中」になる
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_check_in_is_one_time_per_day()
    {
        // テストの日時を固定
        $testDateTime = Carbon::create(2025, 07, 27, , 0, 0);
        Carbon::setTestNow($testDateTime);

        // 1. ステータスが退勤済であるユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 退勤済みのレコードを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(8), // 朝8時に出勤
            'closing_time' => $testDateTime->copy()->subHours(1), // 1時間前に退勤
        ]);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.register'));

        // 2. 勤務ボタンが表示されないことを確認する
        $response->assertStatus(200);
        $response->assertSee('退勤済'); // 「退勤済」ステータスが表示されていることを確認

        // 期待挙動：画面上に「出勤」ボタンが表示されない
        $response->assertDontSee('出勤'); // ヘッダーの「勤怠」に引っかからないように注意
    }
}
