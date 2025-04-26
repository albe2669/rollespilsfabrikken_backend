<?php

namespace Database\Factories;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarFactory extends Factory
{
    protected $model = Calendar::class;

    public function definition(): array
    {
        return [
            'title' => fake()->streetName,
            'description' => fake()->text(200),
            'colour' => fake()->hexColor,
            'obj_id' => function() {
                return (new App\Models\Obj)->create([
                    'type' => 'calendar'
                ])['id'];
            }
        ];
    }
}
