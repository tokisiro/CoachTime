<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 休憩2の有無を50%の確率で決める
    $hasBreak2 = $this->faker->boolean(50);

    // 休憩2の開始・終了時間を設定（nullも有り）
    $take_break2 = $hasBreak2 ? $this->faker->time() : null;
    $return_break2 = $hasBreak2 ? $this->faker->time() : null;

        // 出勤退勤時間をランダムに
    $startTime = $this->faker->dateTimeBetween('07:00:00', '09:00:00')->format('H:i:s');
    $endTime = date('H:i:s', strtotime($startTime . ' + 8 hours'));

        // 休憩時間
    $breakSeconds = 3600;
    if ($hasBreak2 && $take_break2 && $return_break2) {
        $breakSeconds += strtotime($return_break2) - strtotime($take_break2);
    }

        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id, // ユーザIDをランダムに
            'date' => $this->faker->date(), // ランダムな日付
            'attendance' => $startTime, // 出勤時間
            'take_break1' => $this->faker->time(), // 休憩開始
            'return_break1' => $this->faker->time(), // 休憩終了
            'take_break2' => $take_break2, // 休憩2開始
            'return_break2' => $return_break2, // 休憩2終了
            'closing_time' => $endTime, // 終了時間
            'working_hours' => null, // 勤務時間（例：4〜9時間の範囲）
            'remarks' => $this->faker->optional()->text(), // 備考省略可能
        ];
    }

    // 例：ファクトリーの中で、afterCreatingフックを使う
    public function configure()
{
    return $this->afterCreating(function (Attendance $attendance) {
        $start = strtotime($attendance->attendance);
        $end = strtotime($attendance->closing_time);
        $totalSeconds = $end - $start;

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
