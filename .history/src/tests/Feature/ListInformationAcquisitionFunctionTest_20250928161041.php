<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class ListInformationAcquisitionFunctionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $userNoAttendance;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        // テストごとにデータベースをマイグレーションし、シードを実行
        $this->seed('TestDatabaseSeeder');

        // シードされたユーザーを取得
        $this->user = User::where('email', 'test@example.com')->first();
        $this->userNoAttendance = User::where('email', 'noattendance@example.com')->first();
        $this->now = Carbon::now();
    }
}
