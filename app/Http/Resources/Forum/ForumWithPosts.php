<?php

namespace App\Http\Resources\Forum;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumWithPosts extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->uuid,
            'name' => $this->title,
            'description' => $this->description,
            'colour' => $this->colour,
            'priority' => $this->priority,
            'posts' => $this->posts()->count(),
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_posts' => auth()->user()->can('create', [Post::class, $this->resource]),
                'can_add_comments' => auth()->user()->can('create', [Comment::class, $this->resource]),
            ],
        ];
    }
}
