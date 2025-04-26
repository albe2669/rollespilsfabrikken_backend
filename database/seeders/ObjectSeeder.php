<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ObjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Forum::class, 20)->create();
        factory(App\Models\Calendar::class, 20)->create();
    }
}
