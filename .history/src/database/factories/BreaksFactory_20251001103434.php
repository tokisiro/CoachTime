<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Breaks;

class BreaksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $startTime = $this->faker->time('H:i:s'); // H:i:s 形式で生成
        // $endTime が $startTime より後になるようにする
        $endTime = Carbon::parse($startTime)->addMinutes($this->faker->numberBetween(30, 120))->format('H:i:s');

        return [
            'attendance_id' => null,
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
        ];
    }
}
