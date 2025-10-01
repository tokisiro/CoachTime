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
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $generalUser1;
    protected $generalUser2;
    protected $testAttendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを作成
        User::factory()->create(array_merge([
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
            'user_id' => $this->testUser->id,
            'date' => Carbon::parse('2023-01-15'),
            'check_in_time' =>'09:00',
            'closing_time' => '18:00',
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->test1Attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $this->test2Attendance = Attendance::factory()->create([
            'user_id' => $this->test2User->id,
            'date' => Carbon::parse('2023-01-15'),
            'check_in_time' =>'07:00',
            'closing_time' => '16:00',
        ]);

        \App\Models\Breaks::factory()->create([
            'attendance_id' => $this->test2Attendance->id,
            'start_time' => '10:00',
            'end_time' => '11:00',
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
}
