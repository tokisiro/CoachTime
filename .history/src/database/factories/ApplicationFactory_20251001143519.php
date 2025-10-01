<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Application::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::all()->random()->id,
            'attendance_id' => Attendance::all()->random()->id,
            'status' => $this->faker->randomElement(['pending', 'approved',]),
            'reason' => $this->faker->sentence(),
            
            'proposed_closing_time' => $this->faker->time('H:i:s'),
            'proposed_take_break1' => $this->faker->time('H:i:s'),
            'proposed_return_break1' => $this->faker->time('H:i:s'),
            'proposed_take_break2' => $this->faker->time('H:i:s'),
            'proposed_return_break2' => $this->faker->time('H:i:s'),
            'proposed_remarks' => $this->faker->paragraph(),
            'reviewed_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 month', 'now') : null, // 50%の確率で承認日時を設定
            'reviewer_id' => $this->faker->boolean(50) ? User::factory() : null, // 50%の確率で承認者を設定
        ];
    }
}
