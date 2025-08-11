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
        
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id, // ユーザIDをランダムに
            'date' => $this->faker->date(), // ランダムな日付
            'attendance' => $this->faker->time(), // 出勤時間
            'take_break1' => $this->faker->time(), // 休憩開始
            'return_break1' => $this->faker->time(), // 休憩終了
            'take_break2' => $this->faker->time(), // 休憩2開始
            'return_break2' => $this->faker->time(), // 休憩2終了
            'closing_time' => $this->faker->time(), // 終了時間
            'working_hours' => $this->faker->numberBetween(4, 9), // 勤務時間（例：4〜9時間の範囲）
            'remarks' => $this->faker->optional()->text(), // 備考省略可能
        ];
    }
}
