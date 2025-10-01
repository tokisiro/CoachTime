<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
        $response->assertSee('出勤'); // 「出勤」ボタンを想定
        $response->assertDontSee('勤務中'); // まだ出勤していないので「出勤中」ではないことを確認

        // 3. 出勤の処理を行う
        // フォームのアクションが /record-check-in だと仮定
        // ここで送信するデータは通常不要だが、念のため_tokenを含める
        $response = $this->post(route('attendance.recordCheckIn'), [
            '_token' => csrf_token(), // CSRFトークンはテスト中に自動的に生成されるものを利用
        ]);

        // リダイレクトされることを確認 (通常、出勤後は勤怠打刻画面にリダイレクトされる)
        $response->assertRedirect(route('attendance.register'));

        // リダイレクト後の画面を取得
        $response = $this->get(route('attendance.register'));

        // 期待挙動：画面上に表示されるステータスが「出勤中」になる
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertDontSee('出勤'); // 出勤後は「出勤」ボタンが非表示になることを確認（ヘッダーの「勤怠」に引っかからないように注意）
                                         // もし「出勤」ボタンのテキストがフォーム内固有のものであれば、
                                         // より具体的なアサーション（例：特定のCSSセレクタ内のテキスト）を使うと良い
    }
}
