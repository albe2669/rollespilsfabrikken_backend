<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\UserRole\Add;
use App\Http\Requests\Api\Auth\UserRole\Delete;
use App\Http\Requests\Api\Auth\UserRole\Index;
use App\Http\Resources\Role\Role as RoleResource;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

class UserRoleController extends Controller
{
    public function index(Index $request, User $user)
    {
        return response()->json([
            'message' => 'success',
            'roles' => RoleResource::collection($user->roles),
        ]);
    }

    public function add(Add $request, User $user, Role $role)
    {
        if ($user->roles()->get()->where('id', '=', $role['id'])->first() === null) {
            $userRole = (new UserRole);
            $userRole->role()->associate($role);
            $userRole->user()->associate($user);
            $userRole->save();
        }

        return response()->json([
            'message' => 'success',
            'user_roles' => RoleResource::collection($user->roles),
        ]);
    }

    public function delete(Delete $request, User $user, Role $role)
    {
        (new UserRole)
            ->where([
                ['role_id', '=', $role['id']],
                ['user_id', '=', $user['id']],
            ])
            ->get()
            ->each(function (UserRole $userRole, $key) {
                $userRole->delete();
            });

        return response()->json([
            'message' => 'success',
            'user_roles' => RoleResource::collection($user->roles),
        ]);
    }
}
