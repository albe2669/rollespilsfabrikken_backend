<?php


namespace Tests\Helpers;


use App\Models\Role;
use App\Models\User;

class TestHelper
{
    public function giveUserPermission(User $user, $obj_id, $level): void
    {
        $role = Role::factory()->create();

        (new \App\Models\RolePerm)->create([
            'role_id' => $role['id'],
            'permission_id' => (new \App\Models\Permission())
                ->where('obj_id', '=', $obj_id)
                ->where('level', '=', $level)
                ->first()['id']
        ]);

        (new \App\Models\UserRole())->create([
            'role_id' => $role['id'],
            'user_id' => $user['id']
        ]);
    }
}
