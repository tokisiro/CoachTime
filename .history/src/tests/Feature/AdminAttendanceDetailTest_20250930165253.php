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
            'check_in_time' => '09:00:00',
            'closing_time' => '18:00:00',
        ]);

        $this->existingBreak = Breaks::factory()->for($this->testAttendance)->create([
            'start_time' => Carbon::today()->setHour(12)->setMinute(0)->toDateTimeString(),
            'end_time' => Carbon::today()->setHour(13)->setMinute(0)->toDateTimeString(),
        ]);
    }

    /** @test */
    public function attendance_detail_screen_displays_selected_data_correctly()
    {
        // 管理者としてログインし、特定の勤怠詳細画面にアクセス
        // admin.showDetails は、{id} パラメータを受け取ると仮定
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));

        $response->assertOk(); // HTTPステータスコード200を確認

        // 期待挙動: 詳細画面の内容が選択した情報と一致する
        $response->assertSee($this->testUser->name); // ユーザー名
        $response->assertSee('2023年01月15日'); // 日付
        $response->assertSee('2023年01月15日');
        $response->assertSee('09:00'); // 出勤時間
        $response->assertSee('18:00'); // 退勤時間
        $response->assertSee('12:00'); // 休憩開始時間
        $response->assertSee('13:00'); // 休憩終了時間
        $response->assertSee('テスト用の勤怠備考'); // 備考
        // 必要に応じて、他の時間計算結果などもアサートする
    }
}
