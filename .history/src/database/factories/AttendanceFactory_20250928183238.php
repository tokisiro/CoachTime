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

        
    }
}
