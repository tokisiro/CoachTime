<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceRegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    
    public function test_current_datetime_is_displayed_correctly_on_ui()
    {
        $now = Carbon::now();

        $expectedDateStr = $now->isoFormat('YYYY年MM月DD日(ddd)');

        $expectedTimeStr = $now->format('H:i');

        $response = $this->get(route('attendance.register'));

        $response->assertStatus(200);

        $response->assertSee($expectedDateStr);

        $response->assertSee($expectedTimeStr);


    }
}
