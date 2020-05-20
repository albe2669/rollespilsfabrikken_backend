<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class Role extends JsonResource
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
            'title' => $this->title,
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource)
            ]
        ];
    }
}
