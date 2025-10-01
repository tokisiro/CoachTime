<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
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

    
}
