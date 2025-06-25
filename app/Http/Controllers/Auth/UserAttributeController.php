<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\User\Ban;
use App\Http\Requests\API\Auth\User\Op;
use App\Http\Requests\API\Auth\User\Unban;
use App\Http\Resources\User\User as UserResource;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class UserAttributeController extends Controller
{
    /**
     * Ban user
     *
     * @return JsonResponse
     */
    public function ban(Ban $request, User $user)
    {
        if ($user->deleted_at != null) {
            // Unban user
            $user->deleted_at = null;
        } else {
            // Ban user
            $user->deleted_at = Carbon::now()->toDateTimeString();

            // Delete all user tokens, so they cannot lock in
            $user
                ->tokens()
                ->each(function ($item, $key) {
                    $item->delete();
                });
        }

        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Op user
     *
     * @param  Ban  $request
     * @return JsonResponse
     */
    public function op(Op $request, User $user)
    {
        $role = (new Role)
            ->where('title', '=', 'Administrator')
            ->first();

        if ($user->super_user == true) {
            // Deop
            $user->super_user = false;

            // Remove the Administrator role from the user
            (new UserRole)
                ->where([
                    ['role_id', '=', $role['id']],
                    ['user_id', '=', $user['id']],
                ])
                ->get()
                ->each(function (UserRole $userRole, $key) {
                    $userRole->delete();
                });
        } else {
            // OP
            $user->super_user = true;

            // Add the administrator role to the user
            if ($user->roles()->get()->where('id', '=', $role['id'])->first() === null) {
                $userRole = (new UserRole);
                $userRole->role()->associate($role);
                $userRole->user()->associate($user);
                $userRole->save();
            }
        }

        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }
}
