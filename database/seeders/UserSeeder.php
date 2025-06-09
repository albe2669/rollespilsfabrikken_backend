<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
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
        User::factory(50)
            ->create()
            ->each(function ($user) {
                $nums = [1];

                for ($j = 1; $j <= 5; $j++) {
                    $num = 1;
                    while (in_array($num, $nums)) {
                        $num = rand(1, 20);
                    }

                    UserRole::create([
                        'user_id' => $user['id'],
                        'role_id' => $num,
                    ]);
                }
            });
    }
}
