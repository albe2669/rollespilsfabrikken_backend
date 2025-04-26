<?php

namespace Database\Seeders;

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
        factory(App\Models\Comment::class, 100)->create([
            'post_id' => 2
        ])->each(function ($comment) {
            $ids = DB::table('comments')
                ->select('id')
                ->where('id', '<', 8000)
                ->where('post_id', '=', 2)
                ->get();

            $id = rand(1, $ids->count());

            $comment['parent_id'] = $ids[$id-1]->id;
            $comment->save();
        });

        factory(App\Models\Comment::class, 100)->create([
            'post_id' => 2
        ])->each(function ($comment) {
            $ids = DB::table('comments')
                ->select('id')
                ->where('id', '>', 8000)
                ->where('post_id', '=', 2)
                ->get();

            $id = rand(1, $ids->count());

            $comment['parent_id'] = $ids[$id-1]->id;
            $comment->save();
        });
    }
}
