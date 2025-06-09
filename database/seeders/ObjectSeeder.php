<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Forum;
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
        Forum::factory(20)->create();
        Calendar::factory(20)->create();
    }
}
