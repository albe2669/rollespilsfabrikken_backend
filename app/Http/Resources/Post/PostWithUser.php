<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\PostFile\PostFile as PostFileResource;
use App\Http\Resources\User\User as UserResource;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class PostWithUser extends JsonResource
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
            'user' => new UserResource((new User)->find($this->user_id)),
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned,
            'locked' => $this->locked,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments' => $this->comments()->count(),
            'relevance' => $this->when(isset($this->relevance), function () {
                return $this->relevance;
            }),
            'files' => $this->when($this->files, PostFileResource::collection($this->files)),
            'permissions' => [
                'can_pin' => auth()->user()->can('pin', $this->resource),
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_add_comments' => auth()->user()->can('create', [Comment::class, $this->resource->forum]),
            ],
        ];
    }
}
