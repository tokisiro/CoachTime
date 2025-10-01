<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Breaks;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

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
        $date = $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');

        $checkInTime = Carbon::parse($date . ' ' .  $this->faker->dateTimeBetween('08:00:00', '09:30:00')->format('H:i:s'));
        $closingTime =  (clone $checkInTime)->addHours($this->faker->numberBetween(7, 9))->addMinutes($this->faker->numberBetween(0, 59));

        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'date' => $date,
            'check_in_time' => $checkInTime,
            'closing_time' => $closingTime,
            'working_minutes' => null,
            'remarks' => null,
            'status' => $this->faker->randomElement(['submitted', 'pending']),
        ];
    }

    public function configure()
    {

        return $this->afterCreating(function (Attendance $attendance) {
            // check_in_time と closing_time が Carbon インスタンスでない場合はパース
            $checkIn = is_string($attendance->check_in_time) ? Carbon::parse($attendance->check_in_time) : $attendance->check_in_time;
            $closing = is_string($attendance->closing_time) ? Carbon::parse($attendance->closing_time) : $attendance->closing_time;

            if ($checkIn && $closing && $closing->greaterThan($checkIn)) { // closing が checkIn より後であることを確認
                $totalWorkingMinutes = $closing->diffInMinutes($checkIn);

                // 休憩の生成
                // 休憩1（必ず生成）
                $break1Start = (clone $checkIn)->addHours(4); // $checkIn から4時間後
                $break1End = (clone $break1Start)->addMinutes(60); // 60分休憩
                \App\Models\Breaks::factory()->create([ // namespace を追加
                    'attendance_id' => $attendance->id,
                    'start_time' => $break1Start->format('H:i:s'), // H:i:s 形式で保存
                    'end_time' => $break1End->format('H:i:s'),
                ]);


                // 休憩時間を取得して総勤務時間から減算
                $attendance->load('breaks');
                $totalBreakMinutes = $attendance->getTotalBreakMinutesAttribute();

                // 負の値にならないように max(0, ...) で調整
                $actualWorkingMinutes = max(0, $totalWorkingMinutes - $totalBreakMinutes);

                $attendance->update([
                    'working_minutes' => $actualWorkingMinutes,
                ]);
            } else {
                // checkIn または closing が不正な場合、working_minutes は 0 または null に設定
                $attendance->update([
                    'working_minutes' => 0, // あるいは null
                ]);
            }
        });
    }
    }

