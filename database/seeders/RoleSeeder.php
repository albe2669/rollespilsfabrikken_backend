<?php

namespace Database\Seeders;

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
        factory(App\Models\Role::class, 20)
            ->create()
            ->each(function ($role) {
                $nums = array(1);

                for ($j = 1; $j <= 20; $j++) {
                    $num = 1;
                    while (in_array($num, $nums)) {
                        $num = rand(1,110);
                    }

                    (new App\Models\RolePerm)->create([
                        'role_id' => $role['id'],
                        'permission_id' => $num
                    ]);
                }
            });
    }
}
