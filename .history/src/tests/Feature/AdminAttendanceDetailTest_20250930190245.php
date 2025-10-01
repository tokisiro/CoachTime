<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $testUser;
    protected $testAttendance;

    protected function setUp(): void
    {

    

        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        // テスト用の一般ユーザーを作成
        $this->testUser = User::factory()->create(['name' => 'テスト太郎', 'role' => 'employee']);

        // テスト用の勤怠データを作成
        $this->testAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::parse('2023-01-15'),
            'check_in_time' =>'09:00',
            'closing_time' => '18:00',
        ]);

        $this->testAttendance->breaks()->create([
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

    }

    /** @test */
    public function attendance_detail_screen_displays_selected_data_correctly()
    {

    $this->testAttendance->refresh(); // データベースから最新の情報を取得

    dump('Test Attendance ID:', $this->testAttendance->id);
    dump('Database check_in_time (Carbon):', $this->testAttendance->check_in_time);
    dump('Database check_in_time (Formatted):', $this->testAttendance->check_in_time->format('H:i'));
    dump('Database closing_time (Carbon):', $this->testAttendance->closing_time);
    dump('Database closing_time (Formatted):', $this->testAttendance->closing_time->format('H:i'));

    dump('Number of breaks for attendance:', $this->testAttendance->breaks->count());
    foreach ($this->testAttendance->breaks as $index => $break) {
        dump("Break {$index} ID:", $break->id);
        dump("Break {$index} start_time (Carbon):", $break->start_time);
        dump("Break {$index} start_time (Formatted):", $break->start_time->format('H:i'));
        dump("Break {$index} end_time (Carbon):", $break->end_time);
        dump("Break {$index} end_time (Formatted):", $break->end_time->format('H:i'));
    }



        // 管理者としてログインし、特定の勤怠詳細画面にアクセス
        // admin.showDetails は、{id} パラメータを受け取ると仮定
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));

        $response->assertOk(); // HTTPステータスコード200を確認

        // 期待挙動: 詳細画面の内容が選択した情報と一致する
        $response->assertSee($this->testUser->name); // ユーザー名
        $response->assertSee('2023年'); // 日付
        $response->assertSee('1月15日');
        $response->assertSee('09:00'); // 出勤時間
        $response->assertSee('18:00'); // 退勤時間
        $response->assertSee('12:00'); // 休憩開始時間
        $response->assertSee('13:00'); // 休憩終了時間
        // 必要に応じて、他の時間計算結果などもアサートする
    }





    /** @test */
    public function error_message_is_displayed_if_check_in_time_is_after_closing_time()
    {
        // 管理者としてログインし、勤怠詳細の更新を試みる
        // 出勤時間を退勤時間より後に設定
        $invalidData = [
            'check_in_time' => '19:00:00', // 退勤時間より後
            'closing_time' => '18:00:00',
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
            'remarks' => 'テスト備考',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')->put(route('admin.updateAttendance', ['id' => $this->testAttendance->id]), $invalidData);

        // リダイレクトされることを確認 (バリデーションエラーの場合、通常は元のフォームに戻る)
        $response->assertSessionHasErrors(['check_in_time', 'closing_time']); // または一般的な 'time_order' などのキー
        $response->assertStatus(302); // バリデーションエラー時は通常リダイレクト

        // エラーメッセージが表示されていることを確認
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id])); // エラーセッションを持つ状態でページに再アクセス

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です'); // 期待されるエラーメッセージ
    }




    /** @test */
    public function error_message_is_displayed_if_break_start_time_is_after_closing_time()
    {
        // 管理者としてログインし、勤怠詳細の更新を試みる
        // 休憩開始時間を退勤時間より後に設定
        $invalidData = [
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'break_start_time' => '19:00:00', // 退勤時間より後
            'break_end_time' => '20:00:00', // 休憩開始と合わせる
            'remarks' => 'テスト備考',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')->put(route('admin.updateAttendance', ['id' => $this->testAttendance->id]), $invalidData);

        $response->assertSessionHasErrors(['break_start_time', 'closing_time']); // または一般的な 'break_time_order' などのキー
        $response->assertStatus(302);

        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));
        $response->assertSee('休憩時間が不適切な値です'); // 期待されるエラーメッセージ
    }







    /** @test */
    public function error_message_is_displayed_if_break_end_time_is_after_closing_time()
    {
        // 管理者としてログインし、勤怠詳細の更新を試みる
        // 休憩終了時間を退勤時間より後に設定
        $invalidData = [
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'break_start_time' => '17:00:00',
            'break_end_time' => '19:00:00', // 退勤時間より後
            'remarks' => 'テスト備考',
        ];

        $response = $this->actingAs($this->adminUser, 'admin')->put(route('admin.updateAttendance', ['id' => $this->testAttendance->id]), $invalidData);

        $response->assertSessionHasErrors(['break_end_time', 'closing_time']); // または一般的な 'break_time_order' などのキー
        $response->assertStatus(302);

        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です'); // 期待されるエラーメッセージ
    }





    /** @test */
    public function error_message_is_displayed_if_remarks_field_is_empty()
    {
        // 管理者としてログインし、勤怠詳細の更新を試みる
        // 備考欄を空にする
        $invalidData = [
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
            'remarks' => '', // 空の備考
        ];

        $response = $this->actingAs($this->adminUser, 'admin')->put(route('admin.updateAttendance', ['id' => $this->testAttendance->id]), $invalidData);

        $response->assertSessionHasErrors('remarks');
        $response->assertStatus(302);

        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));
        $response->assertSee('備考を記入してください'); // 期待されるエラーメッセージ
    }
}
