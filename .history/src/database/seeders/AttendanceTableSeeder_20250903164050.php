<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = User::pluck('id')->toArray();

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subYears(1);

        foreach ($userIds as $userId) {
            $currentDate = $startDate->copy();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                if (in_array($currentDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    Attendance::factory()->create([
                        'user_id' => $userId,
                        'date' => $currentDate->toDateString(),
                        // 他のパラメータはファクトリー側で自動生成
                    ]);
                }
                $currentDate->addDay();
            }
        }
    }
}
