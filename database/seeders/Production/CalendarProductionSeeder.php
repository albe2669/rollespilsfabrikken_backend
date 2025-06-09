<?php

namespace Database\Seeders\Production;

use App\Models\Calendar;
use App\Models\Obj;
use Faker\Generator;
use Illuminate\Database\Seeder;

class CalendarProductionSeeder extends Seeder
{
    private Generator $faker;

    private function create($title, $description)
    {
        (new Calendar)
            ->fill([
                'title' => $title,
                'description' => $description,
                'colour' => fake()->hexColor,
            ])
            ->obj()
            ->associate((new Obj)->create([
                'type' => 'calendar',
            ]))->save();
    }

    public function run()
    {
        $this->create('Vanbooking', 'Booking af varevogne');
        $this->create('Lokalebooking', 'Booking af lokaler');
        $this->create('Faste arrangementer', 'Faste arrangementer');
        $this->create('Nøglebærere', '');
    }
}
