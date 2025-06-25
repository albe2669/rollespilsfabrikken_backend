<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\CommentFile\CommentFile as CommentFileResource;
use App\Models\Comment as CommentClass;
use Illuminate\Http\Resources\Json\JsonResource;

class Comment extends JsonResource
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
            'user_id' => $this->user->uuid,
            'parent_id' => $this->when($this->parent_id !== null, function () {
                return $this->parent->uuid;
            }),
            'body' => $this->body,
            'files' => $this->when($this->files, CommentFileResource::collection($this->files)),
            'pinned' => $this->pinned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
                'can_pin' => auth()->user()->can('pin', $this->resource),
                'can_add_comments' => auth()->user()->can('create', [CommentClass::class, $this->resource->forum]),
            ],
        ];
    }
}
