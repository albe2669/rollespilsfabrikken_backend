<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Comment::factory(100)->create([
            'post_id' => 2,
        ])->each(function ($comment) {
            $id = Comment::select('id')
                ->where('id', '<', 8000)
                ->where('post_id', '=', 2)
                ->inRandomOrder()
                ->first()->id;

            $comment['parent_id'] = $id;
            $comment->save();
        });
    }
}
