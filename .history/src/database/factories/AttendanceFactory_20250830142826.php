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

        // 休憩時間の長さ（例：30分なら1800秒）
        $breakSeconds = 0;
        if ($attendance->take_break1 && $attendance->return_break1) {
            $breakSeconds += strtotime($attendance->return_break1) - strtotime($attendance->take_break1);
        }
        if ($attendance->take_break2 && $attendance->return_break2) {
            $breakSeconds += strtotime($attendance->return_break2) - strtotime($attendance->take_break2);
        }

        $workingSeconds = $totalSeconds - $breakSeconds;

        // 時間に変換
        $working_hours = $workingSeconds / 3600;

        // 更新
        $attendance->update([
            'working_hours' => $working_hours,
        ]);
    });
}
}
