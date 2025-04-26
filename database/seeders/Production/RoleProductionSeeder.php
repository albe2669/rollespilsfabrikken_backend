<?php

namespace Database\Seeders\Production;

use App\Models\RolePerm;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Faker\Generator;
use Faker\Factory as Faker;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Forum;
use App\Models\Calendar;

class RoleProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title) : Role {
        $role = (new Role())
            ->fill([
                'title' => $title,
                'color' => fake()->hexColor
            ]);
        $role->save();

        return $role->refresh();
    }

    private function getForumFromName($name) {
        return (new Forum)
            ->where('title', '=', $name)
            ->first();
    }

    private function getCalendarFromName($name) {
        return (new Calendar)
            ->where('title', '=', $name)
            ->first();
    }

    private function givePermission(Role $role, $obj, $level) {
        (new RolePerm)->create([
            'role_id' => $role['id'],
            'permission_id' => (new Permission())
                ->where('obj_id', '=', $obj)
                ->where('level', '=', $level)
                ->first()['id']
        ]);
    }

    public function run()
    {
        fake() = Faker::create();

        // Padawan
        self::create('Padawan');

        // Medlem
        $role = self::create('Medlem');
        $forum = self::getForumFromName('Andet');

        self::givePermission($role, $forum['obj_id'], 4);

        // Nøglebærer
        $role = self::create('Nøglebærere');
        $forum = self::getForumFromName('Nøglebærere');
        $calendar = self::getCalendarFromName('Nøglebærere');

        self::givePermission($role, $forum['obj_id'], 4);
        self::givePermission($role, $calendar['obj_id'], 4);

        // Rude Skov Afvikler
        $role = self::create('Rude Skov Afvikler');
        $forum = self::getForumFromName('Rude Skov');

        self::givePermission($role, $forum['obj_id'], 4);

        // Amager Fælled Afvikler
        $role = self::create('Amager Fælled Afvikler');
        $forum = self::getForumFromName('Amager Fælled');

        self::givePermission($role, $forum['obj_id'], 4);

        // Den Magiske Skole Afvikler
        $role = self::create('Den Magiske Skole Afvikler');
        $forum = self::getForumFromName('Den Magiske Skole');

        self::givePermission($role, $forum['obj_id'], 4);

        // Administrator
        $role = self::create('Administrator');

        // Give the admin account the administrator role
        $userRole = (new UserRole());
        $userRole->role()->associate($role);
        $userRole->user()->associate((new User)->where('super_user', '=', '1')->first());
        $userRole->save();
    }
}
