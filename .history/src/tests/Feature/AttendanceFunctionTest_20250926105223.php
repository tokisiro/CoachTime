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

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $this->assertElementDoesNotExist('#checkInButton', $response->getContent());
    }

    public function test_check_in_is_one_time_per_day()
    {
        $testDateTime = Carbon::create(2025, 07, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $testDateTime->toDateString(),
            'check_in_time' => $testDateTime->copy()->subHours(8),
            'closing_time' => $testDateTime->copy()->subHours(1),
        ]);

        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        // 期待挙動：画面上に「出勤」ボタンが表示されない
        $this->assertElementDoesNotExist('#startWorkBtn', $response->getContent());
    }

    public function test_check_in_time_is_visible_on_attendance_list()
    {
        $testDateTime = Carbon::create(2025, 10, 27, 9, 0, 0);
        Carbon::setTestNow($testDateTime);

        $user = User::factory()->create();
        $this->actingAs($user);

        //出勤の処理を行う
        $this->post(route('record.attendance'));

        //勤怠一覧画面を開く
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('10/27(月)');
        $response->assertSee('09:00');

        // 日付も確認したい場合
        $response->assertSee($testDateTime->format('m・d')); // '2023-10-27'
        $response->assertSee($testDateTime->format('Y年m月d日')); // 日本語表記の場合
    }
}
