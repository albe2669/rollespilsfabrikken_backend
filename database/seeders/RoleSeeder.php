<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePerm;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::factory(20)
            ->create()
            ->each(function ($role) {
                $nums = [1];

                for ($j = 1; $j <= 20; $j++) {
                    $num = 1;
                    while (in_array($num, $nums)) {
                        $num = rand(1, 110);
                    }

                    RolePerm::create([
                        'role_id' => $role['id'],
                        'permission_id' => $num,
                    ]);
                }
            });
    }
}
