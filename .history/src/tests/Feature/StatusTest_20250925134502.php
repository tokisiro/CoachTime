<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaks;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    //テスト実行時の現在日時を固定
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }


}
