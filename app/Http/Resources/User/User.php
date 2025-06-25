<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Role\RoleWithoutDelete as Role;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'banned_at' => $this->when($this->deleted_at !== null, $this->deleted_at),
            'super_user' => $this->super_user,
            'roles' => Role::collection($this->roles),
        ];
    }
}
