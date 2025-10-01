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

    }

    /** @test */
    public function attendance_detail_screen_displays_selected_data_correctly()
    {

    $this->testAttendance->refresh(); // データベースから最新の情報を取得
    dump('Database check_in_time:', $this->testAttendance->check_in_time->format('H:i'));
    dump('Database break start_time:', $this->testAttendance->breaks->first()->start_time->format('H:i'));

    $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));

    $response->assertOk();




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
}
