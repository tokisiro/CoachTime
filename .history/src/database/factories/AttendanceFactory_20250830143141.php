<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Breaks;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $checkInTime = $this->faker->dateTimeBetween('08:00:00', '09:30:00')->format('H:i:s');
        $closingTime = $this->faker->dateTimeBetween($checkInTime . ' + 7 hours', $checkInTime . ' + 9 hours')->format('H:i:s');

        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id, // ユーザIDをランダムに
            'date' => $this->faker->date(), // ランダムな日付
            'check_in_time' => $checkInTime,
            'closing_time' => $closingTime,
            'working_minutes' => null,
            'remarks' => $this->faker->optional()->text(), // 備考省略可能
        ];
    }

    public function configure()
    {

        return $this->afterCreating(function (Attendance $attendance) {

        // 勤務時間 (working_minutes) の計算
        $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
        $closing = \Carbon\Carbon::parse($attendance->closing_time);
        $totalWorkingMinutes = $closing->diffInMinutes($checkIn);

        // 休憩の生成
            // 休憩1（必ず生成）
            $break1Start = \Carbon\Carbon::parse($attendance->check_in_time)->addHours(4)->format('H:i:s');
            $break1End = \Carbon\Carbon::parse($break1Start)->addMinutes(60)->format('H:i:s'); // 60分休憩
            Breaks::factory()->create([
                'attendance_id' => $attendance->id,
                'start_time' => $break1Start,
                'end_time' => $break1End,
            ]);

            // 休憩2（50%の確率で生成）
            if ($this->faker->boolean(50)) {
                $break2Start = \Carbon\Carbon::parse($break1End)->addHours(1)->format('H:i:s');
                $break2End = \Carbon\Carbon::parse($break2Start)->addMinutes($this->faker->randomElement([15, 30]))->format('H:i:s'); // 15分または30分休憩
                Breaks::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $break2Start,
                    'end_time' => $break2End,
                ]);
            }
        // 休憩時間を取得して総勤務時間から減算
            $totalBreakMinutes = $attendance->getTotalBreakMinutesAttribute();
            $actualWorkingMinutes = $totalWorkingMinutes - $totalBreakMinutes;

            $attendance->update([
                'working_minutes' => $actualWorkingMinutes,
            ]);
        });
    }
}
