<?php

namespace Database\Seeders\Production;

use App\Models\Calendar;
use App\Models\Forum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePerm;
use App\Models\User;
use App\Models\UserRole;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;

class RoleProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title): Role
    {
        $role = (new Role)
            ->fill([
                'title' => $title,
                'color' => fake()->hexColor,
            ]);
        $role->save();

        return $role->refresh();
    }

    private function getForumFromName($name): Forum
    {
        return (new Forum)
            ->where('title', '=', $name)
            ->first();
    }

    private function getCalendarFromName($name): Calendar
    {
        return (new Calendar)
            ->where('title', '=', $name)
            ->first();
    }

    private function givePermission(Role $role, $obj, $level): void
    {
        (new RolePerm)->create([
            'role_id' => $role['id'],
            'permission_id' => (new Permission)
                ->where('obj_id', '=', $obj)
                ->where('level', '=', $level)
                ->first()['id'],
        ]);
    }

    public function run()
    {
        $this->faker = Faker::create();

        // Padawan
        $this->create('Padawan');

        // Medlem
        $role = $this->create('Medlem');
        $forum = $this->getForumFromName('Andet');

        $this->givePermission($role, $forum['obj_id'], 4);

        // Nøglebærer
        $role = $this->create('Nøglebærere');
        $forum = $this->getForumFromName('Nøglebærere');
        $calendar = $this->getCalendarFromName('Nøglebærere');

        $this->givePermission($role, $forum['obj_id'], 4);
        $this->givePermission($role, $calendar['obj_id'], 4);

        // Rude Skov Afvikler
        $role = $this->create('Rude Skov Afvikler');
        $forum = $this->getForumFromName('Rude Skov');

        $this->givePermission($role, $forum['obj_id'], 4);

        // Amager Fælled Afvikler
        $role = $this->create('Amager Fælled Afvikler');
        $forum = $this->getForumFromName('Amager Fælled');

        $this->givePermission($role, $forum['obj_id'], 4);

        // Den Magiske Skole Afvikler
        $role = $this->create('Den Magiske Skole Afvikler');
        $forum = $this->getForumFromName('Den Magiske Skole');

        $this->givePermission($role, $forum['obj_id'], 4);

        // Administrator
        $role = $this->create('Administrator');

        // Give the admin account the administrator role
        $userRole = (new UserRole);
        $userRole->role()->associate($role);
        $userRole->user()->associate((new User)->where('super_user', '=', '1')->first());
        $userRole->save();
    }
}
