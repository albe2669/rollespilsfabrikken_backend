<?php

namespace Database\Factories;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'calendar_id' => Calendar::factory(),
            'title' => fake()->text,
            'description' => fake()->text(400),
            'start' => fake()->dateTime,
            'end' => fake()->dateTime,
        ];
    }
}
