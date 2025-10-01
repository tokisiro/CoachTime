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
    protected $generalUser1;
    protected $generalUser2;
    protected $testAttendance;

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
        $this->test1Attendance = User::factory()->create(array_merge([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('employeepassword'),
            'role' => 'employee',
        ]));


        $this->test2Attendance = User::factory()->create(array_merge([
            'name' => 'Test Sample',
            'email' => 'sample@example.com',
            'password' => Hash::make('samplepassword'),
            'role' => 'employee',
        ]));

        $this->test1Attendance = Attendance::factory()->create([
            'user_id' => $this->test1Attendance->id,
            'date' => Carbon::parse('2025-10-01'),
            'check_in_time' =>'09:00',
            'closing_time' => '18:00',
        ]);

        $this->first1Break = \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->test1Attendance->id,
            'start_time' => Carbon::parse('2025-10-01 12:00'),
            'end_time' =>  Carbon::parse('2025-10-01 13:00'),
        ]);

        $this->test2Attendance = Attendance::factory()->create([
            'user_id' => $this->test2Attendance->id,
            'date' => Carbon::parse('2025-10-01')、
            'check_in_time' =>'07:00',
            'closing_time' => '16:00',
        ]);

        $this->first2Break = \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->test2Attendance->id,
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
        $response->assertSee($this->test1Attendance->name);
        $response->assertSee($this->test1Attendance->email);
        $response->assertSee($this->test2Attendance->name);
        $response->assertSee($this->test2Attendance->email);

        // 管理者自身の情報は表示されないことを確認
        $response->assertDontSee($this->adminUser->name);
        $response->assertDontSee($this->adminUser->email);
    }


    /** @test */
    public function admin_can_view_a_users_attendance_list()
    {
        $response = $this->actingAs($this->adminUser, 'admin');

        $response = $this->get(route('admin.attendances.staff.list', ['user' => $this->test1Attendance->user_id]));

        // 期待挙動: ステータスコード200 (OK)
        $response->assertStatus(200);

        // 勤怠情報が正確に表示される
        // 日付、出勤時間、退勤時間などが表示されていることを確認
        $response->assertSee(Carbon::parse($this->test1Attendance->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee(Carbon::parse($this->test1Attendance->check_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($this->test1Attendance->closing_time)->format('H:i'));

        // 休憩時間も表示されているか確認 (休憩時間がある場合)
        $totalBreakMinutes = 0;
        foreach ($this->test1Attendance->breaks as $break) {
            if ($break->start_time && $break->end_time) {
                $breakStart = Carbon::parse($break->start_time);
                $breakEnd = Carbon::parse($break->end_time);
                $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
            }
        }
        $expectedTotalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        $response->assertSee($expectedTotalBreakTime);
    }
}
