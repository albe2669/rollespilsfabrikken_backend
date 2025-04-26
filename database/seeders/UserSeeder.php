<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\User::class, 50)
            ->create()
            ->each(function ($user) {
                $nums = array(1);

                for ($j = 1; $j <= 5; $j++) {
                    $num = 1;
                    while (in_array($num, $nums)) {
                        $num = rand(1,20);
                    }

                    (new App\Models\UserRole)->create([
                        'user_id' => $user['id'],
                        'role_id' => $num
                    ]);
                }
            });
    }
}
