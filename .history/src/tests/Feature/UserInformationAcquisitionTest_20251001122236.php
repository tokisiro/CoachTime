<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaks;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserInformationAcquisitionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $test1Attendance;
    protected $test2Attendance;
    protected $first1Break;
    protected $first2Break;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setLocale('ja');

        // 管理者ユーザーを作成
        $this->adminUser = User::factory()->create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'admin',
        ]));

        // 一般ユーザーを作成
        $this->employeeUser1 = User::factory()->create(array_merge([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('employeepassword'),
            'role' => 'employee',
        ]));

        $targetDate = Carbon::parse('2025-10-01');

        $this->attendanceRecord1 = Attendance::factory()->create([
            'user_id' => $this->employeeUser1->id,
            'date' => Carbon::today(),
            'check_in_time' =>$targetDate->copy()->setHour(9)->setMinute(0),
            'closing_time' => $targetDate->copy()->setHour(18)->setMinute(0),
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => $targetDate->copy()->setHour(12)->setMinute(0)->format('H:i:s'),
            'end_time' =>  $targetDate->copy()->setHour(13)->setMinute(0)->format('H:i:s'),
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => $targetDate->copy()->setHour(14)->setMinute(0)->format('H:i:s'),
            'end_time' =>  $targetDate->copy()->setHour(15)->setMinute(0)->format('H:i:s'), // +1時間
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord1->id,
            'start_time' => $targetDate->copy()->setHour(16)->setMinute(0)->format('H:i:s'),
            'end_time' =>  $targetDate->copy()->setHour(17)->setMinute(0)->format('H:i:s'), // +1時間
        ]);

        $this->attendanceRecord1 = $this->attendanceRecord1->fresh('breaks');


        $this->employeeUser2 = User::factory()->create(array_merge([
            'name' => 'Test Sample',
            'email' => 'sample@example.com',
            'password' => Hash::make('samplepassword'),
            'role' => 'employee',
        ]));

        $this->attendanceRecord2 = Attendance::factory()->create([
            'user_id' => $this->employeeUser2->id,
            'date' => Carbon::today(),
            'check_in_time' =>'07:00',
            'closing_time' => '16:00',
        ]);

        $this->break1ForAttendance2 = \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->attendanceRecord2->id,
            'start_time' => Carbon::parse('2025-10-01 15:00:00'),
            'end_time' => Carbon::parse('2025-10-01 15:30:00'),
        ]);

    }

    /** @test */
    public function admin_can_view_all_general_users_names_and_emails()
    {
        // 管理者でログインする
        $response = $this->actingAs($this->adminUser, 'admin');

        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);

        // 全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
        $response->assertSee($this->employeeUser1->name);
        $response->assertSee($this->employeeUser1->email);
        $response->assertSee($this->employeeUser2->name);
        $response->assertSee($this->employeeUser2->email);

        // 管理者自身の情報は表示されないことを確認
        $response->assertDontSee($this->adminUser->name);
        $response->assertDontSee($this->adminUser->email);
    }


    /** @test */
    public function admin_can_view_a_users_attendance_list()
    {
        $response = $this->actingAs($this->adminUser, 'admin');

        $response = $this->get(route('admin.attendances.staff.list', [
            'user' => $this->employeeUser1->id,
            'year' => Carbon::parse($this->attendanceRecord1->date)->year,
            'month' => Carbon::parse($this->attendanceRecord1->date)->month,]));

        // 期待挙動: ステータスコード200 (OK)
        $response->assertStatus(200);

        // 勤怠情報が正確に表示される
        // 日付、出勤時間、退勤時間などが表示されていることを確認
        $response->assertSee(Carbon::parse($this->attendanceRecord1->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee(Carbon::parse($this->attendanceRecord1->check_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($this->attendanceRecord1->closing_time)->format('H:i'));

        // 休憩時間も表示されているか確認 (休憩時間がある場合)
        $totalBreakMinutes = 0;
        foreach ($this->attendanceRecord1->breaks as $break) {
            if ($break->start_time && $break->end_time) {
                $breakStart = Carbon::parse($break->start_time);
                $breakEnd = Carbon::parse($break->end_time);
                $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
            }
        }
        $expectedTotalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        $response->assertSee($expectedTotalBreakTime);
    }

    /** @test */
    public function admin_can_view_previous_months_attendance_data()
    {
        // 前月の勤怠データも作成しておく
        $lastMonthAttendance = Attendance::factory()->create([
            'user_id' => $this->employeeUser1->id,
            'date' => Carbon::today()->subMonth()->toDateString(),
            'check_in_time' => Carbon::today()->subMonth()->hour(9)->minute(0),
            'closing_time' => Carbon::today()->subMonth()->hour(18)->minute(0),
        ]);

        $this->actingAs($this->adminUser, 'admin');

        $response = $this->get(route('admin.attendances.staff.list', [
            'user' => $this->employeeUser1->id,
            'year' => Carbon::parse($this->attendanceRecord1->date)->year,
            'month' => Carbon::parse($this->attendanceRecord1->date)->month,]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y年m月')); // 現在月が表示されているか確認

        $previousMonth = Carbon::today()->subMonth()->month;

        //「前月」ボタンを押す
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendances.staff.list', [
    'user' => $this->employeeUser1->id,
    'month' => $previousMonth // 前月の月 (整数)
]));

        // 期待挙動: ステータスコード200 (OK)
        $response->assertStatus(200);

        // 前月の情報が表示されている
        $response->assertSee(Carbon::today()->subMonth()->format('Y年n月')); // 前月が表示されているか確認

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $japaneseDayOfWeek = $weekdays[($this->attendanceRecord1->date)->dayOfWeek];

        $response->assertSee(Carbon::parse($lastMonthAttendance->date)->format('Y年n月')); // 前月の勤怠データが表示されているか
        $response->assertDontSee(Carbon::parse($this->attendanceRecord1->date)->format('Y/m/d')); // 今月の勤怠データは表示されないか
    }






    /** @test */
    public function admin_can_view_next_months_attendance_data()
    {
        // 翌月の勤怠データも作成しておく
        $nextMonthAttendance = Attendance::factory()->create([
            'user_id' => $this->employeeUser1->id,
            'date' => Carbon::today()->addMonth()->toDateString(),
            'check_in_time' => Carbon::today()->addMonth()->hour(9)->minute(0)->second(0),
            'check_out_time' => Carbon::today()->addMonth()->hour(18)->minute(0)->second(0),
        ]);

        $this->actingAs($this->adminUser, 'admin');

        // 勤怠一覧ページを開く (現在月を表示するルートを想定)
        $currentMonth = Carbon::today()->format('Y-m');
        $response = $this->get(route('admin.attendances.staff.list', [
            'user' => $this->employeeUser1->id,
            'month' => $currentMonth
        ]));
        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y年n月'));

        // 「翌月」ボタンを押す
        $nextMonth = Carbon::today()->addMonth()->format('Y-m');
        $response = $this->get(route('admin.attendances.staff.list', [
            'user' => $this->generalUser1->id,
            'month' => $nextMonth
        ]));

        // 期待挙動: ステータスコード200 (OK)
        $response->assertStatus(200);

        // 翌月の情報が表示されている
        $response->assertSee(Carbon::today()->addMonth()->format('Y年m月')); // 翌月が表示されているか確認
        $response->assertSee(Carbon::parse($nextMonthAttendance->date)->format('Y/m/d')); // 翌月の勤怠データが表示されているか
        $response->assertDontSee(Carbon::parse($this->testAttendance->date)->format('Y/m/d')); // 今月の勤怠データは表示されないか
    }




    /** @test */
    public function admin_can_navigate_to_attendance_details_page()
    {
        // 1. 管理者ユーザーにログインをする
        $this->actingAs($this->adminUser, 'admin');

        // 2. 勤怠一覧ページを開く (直接勤怠詳細ページに遷移するので、一覧ページは必須ではないがシナリオに沿う)
        // 例えば、一覧ページから詳細ページへのリンクが貼られていることを想定する
        // 今回は直接詳細ページにアクセスする形でテスト
        // ルート名が 'admin.attendances.show' または 'admin.showDetails' のような形を想定
        $response = $this->get(route('admin.showDetails', ['id' => $this->testAttendance->id]));

        // 期待挙動: ステータスコード200 (OK)
        $response->assertStatus(200);

        // その日の勤怠詳細画面に遷移する
        // 詳細ページに表示されるべき情報があることを確認
        $response->assertSee(Carbon::parse($this->testAttendance->date)->format('Y年m月d日'));
        $response->assertSee(Carbon::parse($this->testAttendance->check_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($this->testAttendance->check_out_time)->format('H:i'));
        $response->assertSee('承認状態'); // または承認ステータスが表示されていることを確認
        $response->assertSee($this->generalUser1->name . 'さんの勤怠詳細'); // ユーザー名が表示されているか
    }
}

