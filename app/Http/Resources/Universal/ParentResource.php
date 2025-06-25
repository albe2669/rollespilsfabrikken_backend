<?php

namespace App\Http\Resources\Universal;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ParentResource extends JsonResource
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
            'type' => mb_strtolower(Str::replaceFirst('App\\Models\\', '', get_class($this->resource))),
        ];
    }
}
