<?php

namespace Database\Factories;

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'forum_id' => Forum::factory(),
            'title' => fake()->text,
            'body' => fake()->text(400),
        ];
    }
}
