<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = [1, 2, 3, 4, 5, ]; // ユーザIDリスト
        
        $startDate = Carbon::today()->subMonths(3);
        $endDate = Carbon::today();

        foreach ($userIds as $userId) {
            $currentDate = $startDate->copy();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                if (in_array($currentDate->dayOfWeek, [1, 2, 3, 4, 5])) { // 月〜金
                    \App\Models\Attendance::factory()->create([
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
