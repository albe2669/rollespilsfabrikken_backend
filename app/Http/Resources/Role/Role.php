<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\Permission\PermissionWithoutDelete;
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
            'color' => $this->color,
            'show' => $this->show,
            'role_permissions' => PermissionWithoutDelete::collection($this->permissions),
            'permissions' => [
                'can_update' => auth()->user()->can('update', $this->resource),
                'can_delete' => auth()->user()->can('delete', $this->resource),
            ],
        ];
    }
}
