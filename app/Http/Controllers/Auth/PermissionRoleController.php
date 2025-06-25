<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RolePerm\Add;
use App\Http\Requests\API\Auth\RolePerm\CalendarAdd;
use App\Http\Requests\API\Auth\RolePerm\CalendarIndex;
use App\Http\Requests\API\Auth\RolePerm\ForumAdd;
use App\Http\Requests\API\Auth\RolePerm\ForumIndex;
use App\Http\Requests\API\Auth\RolePerm\MultiAdd;
use App\Http\Requests\API\Auth\RolePerm\MultiDelete;
use App\Http\Resources\Permission\PermissionWithoutParent;
use App\Http\Resources\Role\RoleWithPermissions;
use App\Models\Calendar;
use App\Models\Forum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePerm;

class PermissionRoleController extends Controller
{
    // Get or create roleperm
    private function createRolePerm(Permission $permission, Role $role)
    {
        $rolePerm = (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']],
            ])->first();

        if ($rolePerm === null) {
            $rolePerm = (new RolePerm);
            $rolePerm->role()->associate($role);
            $rolePerm->permission()->associate($permission);

            $rolePerm->save();
        }
    }

    private function deleteRolePerm(Permission $permission, Role $role)
    {
        (new RolePerm)
            ->where([
                ['permission_id', '=', $permission['id']],
                ['role_id', '=', $role['id']],
            ])->get()
            ->each(function (RolePerm $rolePerm, $key) {
                $rolePerm->delete();

                return true;
            });
    }

    private function getRolePermsWithObjId(Role $role, $obj)
    {
        return $role
            ->permissions()
            ->where('obj_id', '=', $obj)
            ->get();
    }

    private function filterObjPerms($obj, $perms)
    {
        return (new Permission)
            ->where('obj_id', '=', $obj)
            ->whereNotIn('id', $perms)
            ->get();
    }

    public function calendarIndex(CalendarIndex $request, Role $role, Calendar $calendar)
    {
        $perms = $this->getRolePermsWithObjId($role, $calendar['obj_id']);

        return response()->json([
            'message' => 'success',
            'permissions_enabled' => PermissionWithoutParent::collection(
                $perms,
            ),
            'permissions_disabled' => PermissionWithoutParent::collection(
                self::filterObjPerms($calendar['obj_id'], $perms->pluck('id')),
            ),
        ]);
    }

    public function forumIndex(ForumIndex $request, Role $role, Forum $forum)
    {
        $perms = $this->getRolePermsWithObjId($role, $forum['obj_id']);

        return response()->json([
            'message' => 'success',
            'permissions_enabled' => PermissionWithoutParent::collection(
                $perms,
            ),
            'permissions_disabled' => PermissionWithoutParent::collection(
                self::filterObjPerms($forum['obj_id'], $perms),
            ),
        ]);
    }

    public function add(Permission $permission, Role $role)
    {
        self::createRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role),
        ]);
    }

    public function delete(Permission $permission, Role $role)
    {
        self::deleteRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
        ]);
    }

    public function multiAdd(MultiAdd $request, Role $role)
    {
        $perms = $request->validated()['permissions'];

        foreach ($perms as $perm) {
            $permission = Permission::whereUuid($perm)->first();

            if ($permission != null) {
                self::createRolePerm($permission, $role);
            }
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role),
        ]);
    }

    public function multiDelete(MultiDelete $request, Role $role)
    {
        $perms = $request->validated()['permissions'];

        foreach ($perms as $perm) {
            $permission = Permission::whereUuid($perm)->first();

            if ($permission != null) {
                self::deleteRolePerm($permission, $role);
            }
        }

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role),
        ]);
    }

    public function calendarAdd(CalendarAdd $request, Calendar $calendar, $level, Role $role)
    {
        $permission = $calendar
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        self::createRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role),
        ]);
    }

    public function forumAdd(ForumAdd $request, Forum $forum, $level, Role $role)
    {
        $permission = $forum
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        self::createRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
            'role' => new RoleWithPermissions($role),
        ]);
    }

    public function calendarDelete(CalendarAdd $request, Calendar $calendar, $level, Role $role)
    {
        $permission = $calendar
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        self::deleteRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
        ]);
    }

    public function forumDelete(ForumAdd $request, Forum $forum, $level, Role $role)
    {
        $permission = $forum
            ->permissions()
            ->where('level', '=', $level)
            ->first();

        self::deleteRolePerm($permission, $role);

        return response()->json([
            'message' => 'success',
        ]);
    }

    // These functions dont actually do anything they just take the arguments and give them in the right order, to allow multiple routes
    public function roleAdd(Add $request, Role $role, Permission $permission)
    {
        return self::add($permission, $role);
    }

    public function permissionAdd(Add $request, Permission $permission, Role $role)
    {
        return self::add($permission, $role);
    }

    public function roleDelete(Add $request, Role $role, Permission $permission)
    {
        return self::delete($permission, $role);
    }

    public function permissionDelete(Add $request, Permission $permission, Role $role)
    {
        return self::delete($permission, $role);
    }
}
