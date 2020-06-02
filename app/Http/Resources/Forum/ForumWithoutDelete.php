<?php

namespace App\Http\Resources\Forum;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Resources\Json\JsonResource;

class ForumWithoutDelete extends JsonResource
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
        ];
    }
}
