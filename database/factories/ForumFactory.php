<?php

namespace Database\Factories;

use App\Models\Forum;
use App\Models\Obj;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumFactory extends Factory
{
    protected $model = Forum::class;

    public function definition()
    {
        return [
            'title' => fake()->streetName,
            'description' => fake()->text(200),
            'colour' => fake()->hexColor,
            'obj_id' => function() {
                return (new Obj)->create([
                    'type' => 'forum'
                ])['id'];
            }
        ];
    }
}
