<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
        ], $attributes));

        // 一般ユーザーを作成
        User::factory()->create(array_merge([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'employee',
        ], $attributes));


        User::factory()->create(array_merge([
            'name' => 'Test Sample',
            'email' => 'employee@example.com',
            'password' => Hash::make('adminpassword'),
            'role' => 'employee',
        ], $attributes));

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
}
