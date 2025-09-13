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
        $startDate = $endDate->copy()->subYears(5);

        foreach ($userIds as $userId) {
            $currentDate = $startDate->copy();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                if ($currentDate->isWeekend()) { // Carbon の isWeekend() メソッドが便利
                    // 土日の場合は出勤していないデータを作成
                    Attendance::factory()->create([
                        'user_id' => $userId,
                        'date' => $currentDate->toDateString(),
                        'check_in_time' => null, // 出勤時間なし
                        'closing_time' => null,  // 退勤時間なし
                        'working_minutes' => null, // 勤務時間なし
                        'remarks' => null, // 備考もなし
                        'status' => 'submitted', // 土日は確定済みの休みとする
                    ]);
                } else {
                    // 平日の場合は通常の勤務データを作成
                    Attendance::factory()->create([
                        'user_id' => $userId,
                        'date' => $currentDate->toDateString(),
                        // check_in_time, closing_time, working_minutes, remarks, status はファクトリーで生成
                    ]);
                }
                $currentDate->addDay();
            }
        }
    }
}
