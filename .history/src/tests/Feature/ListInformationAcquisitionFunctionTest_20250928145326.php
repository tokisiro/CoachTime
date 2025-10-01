<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ListInformationAcquisitionFunctionTest extends TestCase
{
    use RefreshDatabase;
    

    
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
