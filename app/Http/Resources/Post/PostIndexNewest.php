<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\Forum\Forum;
use App\Http\Resources\User\User as UserResource;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class PostIndexNewest extends JsonResource
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
            'parent' => new Forum($this->forum),
            'user' => new UserResource((new User)->find($this->user_id)),
            'title' => $this->title,
            'pinned' => $this->pinned,
            'locked' => $this->locked,
            'has_files' => $this->files->count() > 0,
            'comments' => $this->comments()->count(),
            'relevance' => $this->when(isset($this->relevance), function () {
                return $this->relevance;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'permissions' => [
                'can_pin' => auth()->user()->can('pin', $this->resource),
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_comments' => auth()->user()->can('create', [Comment::class, $this->resource->forum]),
            ],
        ];
    }
}
