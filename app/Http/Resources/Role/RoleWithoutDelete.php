<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\Permission\Permission;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleWithoutDelete extends JsonResource
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
            'role_permissions' => Permission::collection($this->permissions),
    	];
    }
}
